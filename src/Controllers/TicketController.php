<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuditLogger;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use PDO;

class TicketController
{
    private const STATUSES = ['new', 'open', 'in_progress', 'waiting_for_user', 'resolved', 'closed'];
    private const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    private PDO $pdo;
    private Auth $auth;
    private Csrf $csrf;
    private AuditLogger $audit;

    public function __construct(PDO $pdo, Auth $auth, Csrf $csrf, AuditLogger $audit)
    {
        $this->pdo = $pdo;
        $this->auth = $auth;
        $this->csrf = $csrf;
        $this->audit = $audit;
    }

    public function categories(): void
    {
        $this->auth->requireLogin();
        $stmt = $this->pdo->query(
            'SELECT id, name, description, color
             FROM categories
             WHERE is_active = 1
             ORDER BY name'
        );

        Response::success(['items' => $stmt->fetchAll()]);
    }

    public function index(Request $request): void
    {
        $user = $this->auth->requireLogin();
        $page = max(1, (int)$request->query('page', 1));
        $perPage = min(50, max(5, (int)$request->query('per_page', 10)));
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if (!$this->auth->canManageTickets($user)) {
            $where[] = 't.author_id = :author_id';
            $params['author_id'] = (int)$user['id'];
        }

        $status = (string)$request->query('status', '');
        if ($status !== '' && Validator::enum($status, self::STATUSES)) {
            $where[] = 't.status = :status';
            $params['status'] = $status;
        }

        $priority = (string)$request->query('priority', '');
        if ($priority !== '' && Validator::enum($priority, self::PRIORITIES)) {
            $where[] = 't.priority = :priority';
            $params['priority'] = $priority;
        }

        $categoryId = $request->query('category_id');
        if ($categoryId !== null && $categoryId !== '' && Validator::intInRange($categoryId, 1, 999999)) {
            $where[] = 't.category_id = :category_id';
            $params['category_id'] = (int)$categoryId;
        }

        $search = Validator::cleanString($request->query('search', ''));
        if ($search !== '') {
            $where[] = '(t.title LIKE :search OR t.description LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = $where === [] ? '1 = 1' : implode(' AND ', $where);

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM tickets t WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sortMap = [
            'created_at' => 't.created_at',
            'updated_at' => 't.updated_at',
            'priority' => 't.priority',
            'status' => 't.status',
            'title' => 't.title',
        ];
        $sort = (string)$request->query('sort', 'created_at');
        $sortColumn = $sortMap[$sort] ?? 't.created_at';
        $direction = strtolower((string)$request->query('direction', 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $sql = "SELECT t.id, t.title, t.status, t.priority, t.created_at, t.updated_at,
                       c.name AS category_name, c.color AS category_color,
                       author.name AS author_name, assigned.name AS assigned_name
                FROM tickets t
                JOIN categories c ON c.id = t.category_id
                JOIN users author ON author.id = t.author_id
                LEFT JOIN users assigned ON assigned.id = t.assigned_to
                WHERE $whereSql
                ORDER BY $sortColumn $direction
                LIMIT $perPage OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        Response::success([
            'items' => $stmt->fetchAll(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function show(Request $request, array $params): void
    {
        $user = $this->auth->requireLogin();
        $ticket = $this->loadTicket((int)$params['id']);
        $this->assertCanView($ticket, $user);
        $canManage = $this->auth->canManageTickets($user);

        $commentsSql = 'SELECT tc.id, tc.body, tc.is_internal, tc.created_at,
                               u.id AS author_id, u.name AS author_name, r.name AS author_role
                        FROM ticket_comments tc
                        JOIN users u ON u.id = tc.author_id
                        JOIN roles r ON r.id = u.role_id
                        WHERE tc.ticket_id = :ticket_id';

        if (!$canManage) {
            $commentsSql .= ' AND tc.is_internal = 0';
        }

        $commentsSql .= ' ORDER BY tc.created_at ASC';
        $commentsStmt = $this->pdo->prepare($commentsSql);
        $commentsStmt->execute(['ticket_id' => (int)$ticket['id']]);

        $history = [];
        if ($canManage) {
            $historyStmt = $this->pdo->prepare(
                'SELECT h.id, h.old_status, h.new_status, h.note, h.created_at, u.name AS changed_by_name
                 FROM ticket_status_history h
                 JOIN users u ON u.id = h.changed_by
                 WHERE h.ticket_id = :ticket_id
                 ORDER BY h.created_at ASC'
            );
            $historyStmt->execute(['ticket_id' => (int)$ticket['id']]);
            $history = $historyStmt->fetchAll();
        }

        Response::success([
            'ticket' => $ticket,
            'comments' => $commentsStmt->fetchAll(),
            'history' => $history,
            'can_manage' => $canManage,
        ]);
    }

    public function create(Request $request): void
    {
        $user = $this->auth->requireLogin();
        $this->csrf->assertValid($request);
        $data = $request->json();
        $title = Validator::cleanString($data['title'] ?? '');
        $description = Validator::cleanString($data['description'] ?? '');
        $priority = Validator::cleanString($data['priority'] ?? 'medium');
        $categoryId = (int)($data['category_id'] ?? 0);
        $errors = $this->validateTicketInput($title, $description, $priority, $categoryId);

        if ($errors !== []) {
            throw new HttpException(422, 'Požadavek obsahuje chyby.', $errors);
        }

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare(
            'INSERT INTO tickets (category_id, author_id, title, description, priority, status)
             VALUES (:category_id, :author_id, :title, :description, :priority, :status)'
        );
        $stmt->execute([
            'category_id' => $categoryId,
            'author_id' => (int)$user['id'],
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'status' => 'new',
        ]);
        $ticketId = (int)$this->pdo->lastInsertId();

        $history = $this->pdo->prepare(
            'INSERT INTO ticket_status_history (ticket_id, changed_by, old_status, new_status, note)
             VALUES (:ticket_id, :changed_by, NULL, :new_status, :note)'
        );
        $history->execute([
            'ticket_id' => $ticketId,
            'changed_by' => (int)$user['id'],
            'new_status' => 'new',
            'note' => 'Požadavek vytvořen.',
        ]);

        $this->audit->log((int)$user['id'], 'ticket.created', 'ticket', $ticketId);
        $this->pdo->commit();

        Response::success(['ticket' => $this->loadTicket($ticketId)], 'Požadavek byl vytvořen.', 201);
    }

    public function update(Request $request, array $params): void
    {
        $user = $this->auth->requireLogin();
        $this->csrf->assertValid($request);
        $ticket = $this->loadTicket((int)$params['id']);
        $this->assertCanView($ticket, $user);
        $data = $request->json();
        $canManage = $this->auth->canManageTickets($user);

        if (!$canManage && !in_array($ticket['status'], ['new', 'open', 'waiting_for_user'], true)) {
            throw new HttpException(403, 'Tento požadavek už nemůžete upravit.');
        }

        $updates = [];
        $sqlParams = ['id' => (int)$ticket['id']];
        $errors = [];

        if (array_key_exists('title', $data)) {
            $title = Validator::cleanString($data['title']);
            if (!Validator::length($title, 5, 160)) {
                $errors['title'] = 'Název musí mít 5 až 160 znaků.';
            }
            $updates[] = 'title = :title';
            $sqlParams['title'] = $title;
        }

        if (array_key_exists('description', $data)) {
            $description = Validator::cleanString($data['description']);
            if (!Validator::length($description, 10, 5000)) {
                $errors['description'] = 'Popis musí mít 10 až 5000 znaků.';
            }
            $updates[] = 'description = :description';
            $sqlParams['description'] = $description;
        }

        if (array_key_exists('priority', $data)) {
            $priority = Validator::cleanString($data['priority']);
            if (!Validator::enum($priority, self::PRIORITIES)) {
                $errors['priority'] = 'Neplatná priorita.';
            }
            $updates[] = 'priority = :priority';
            $sqlParams['priority'] = $priority;
        }

        if (array_key_exists('category_id', $data)) {
            $categoryId = (int)$data['category_id'];
            if (!$this->categoryExists($categoryId)) {
                $errors['category_id'] = 'Vybraná kategorie neexistuje.';
            }
            $updates[] = 'category_id = :category_id';
            $sqlParams['category_id'] = $categoryId;
        }

        $statusChanged = false;
        $newStatus = null;

        if ($canManage && array_key_exists('status', $data)) {
            $newStatus = Validator::cleanString($data['status']);
            if (!Validator::enum($newStatus, self::STATUSES)) {
                $errors['status'] = 'Neplatný stav požadavku.';
            }
            if ($newStatus !== $ticket['status']) {
                $statusChanged = true;
                $updates[] = 'status = :status';
                $sqlParams['status'] = $newStatus;
                $updates[] = 'resolved_at = :resolved_at';
                $updates[] = 'closed_at = :closed_at';
                $sqlParams['resolved_at'] = $newStatus === 'resolved' || $newStatus === 'closed' ? date('Y-m-d H:i:s') : null;
                $sqlParams['closed_at'] = $newStatus === 'closed' ? date('Y-m-d H:i:s') : null;
            }
        }

        if ($canManage && array_key_exists('assigned_to', $data)) {
            $assignedTo = $data['assigned_to'] === null || $data['assigned_to'] === '' ? null : (int)$data['assigned_to'];
            if ($assignedTo !== null && !$this->isTechnicianOrAdmin($assignedTo)) {
                $errors['assigned_to'] = 'Požadavek lze přiřadit jen technikovi nebo administrátorovi.';
            }
            $updates[] = 'assigned_to = :assigned_to';
            $sqlParams['assigned_to'] = $assignedTo;
        }

        if (!$canManage && (array_key_exists('status', $data) || array_key_exists('assigned_to', $data))) {
            throw new HttpException(403, 'Běžný uživatel nemůže měnit stav ani přiřazení požadavku.');
        }

        if ($errors !== []) {
            throw new HttpException(422, 'Požadavek obsahuje chyby.', $errors);
        }

        if ($updates === []) {
            Response::success(['ticket' => $ticket], 'Nebyly provedeny žádné změny.');
        }

        $this->pdo->beginTransaction();
        $sql = 'UPDATE tickets SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($sqlParams);

        if ($statusChanged && $newStatus !== null) {
            $history = $this->pdo->prepare(
                'INSERT INTO ticket_status_history (ticket_id, changed_by, old_status, new_status, note)
                 VALUES (:ticket_id, :changed_by, :old_status, :new_status, :note)'
            );
            $history->execute([
                'ticket_id' => (int)$ticket['id'],
                'changed_by' => (int)$user['id'],
                'old_status' => $ticket['status'],
                'new_status' => $newStatus,
                'note' => Validator::cleanString($data['status_note'] ?? 'Stav změněn.'),
            ]);
        }

        $this->audit->log((int)$user['id'], 'ticket.updated', 'ticket', (int)$ticket['id'], ['status_changed' => $statusChanged]);
        $this->pdo->commit();

        Response::success(['ticket' => $this->loadTicket((int)$ticket['id'])], 'Požadavek byl uložen.');
    }

    public function delete(Request $request, array $params): void
    {
        $user = $this->auth->requireLogin();
        $this->csrf->assertValid($request);
        $ticket = $this->loadTicket((int)$params['id']);

        $isAdmin = $this->auth->hasAnyRole($user, ['ADMIN']);
        $isOwnerOfNewTicket = (int)$ticket['author_id'] === (int)$user['id'] && $ticket['status'] === 'new';

        if (!$isAdmin && !$isOwnerOfNewTicket) {
            throw new HttpException(403, 'Tento požadavek nemůžete odstranit.');
        }

        $stmt = $this->pdo->prepare('DELETE FROM tickets WHERE id = :id');
        $stmt->execute(['id' => (int)$ticket['id']]);
        $this->audit->log((int)$user['id'], 'ticket.deleted', 'ticket', (int)$ticket['id'], ['title' => $ticket['title']]);

        Response::success([], 'Požadavek byl odstraněn.');
    }

    public function addComment(Request $request, array $params): void
    {
        $user = $this->auth->requireLogin();
        $this->csrf->assertValid($request);
        $ticket = $this->loadTicket((int)$params['id']);
        $this->assertCanView($ticket, $user);
        $data = $request->json();
        $body = Validator::cleanString($data['body'] ?? '');

        if (!Validator::length($body, 2, 5000)) {
            throw new HttpException(422, 'Komentář obsahuje chyby.', ['body' => 'Komentář musí mít 2 až 5000 znaků.']);
        }

        $isInternal = $this->auth->canManageTickets($user) && !empty($data['is_internal']);
        $stmt = $this->pdo->prepare(
            'INSERT INTO ticket_comments (ticket_id, author_id, body, is_internal)
             VALUES (:ticket_id, :author_id, :body, :is_internal)'
        );
        $stmt->execute([
            'ticket_id' => (int)$ticket['id'],
            'author_id' => (int)$user['id'],
            'body' => $body,
            'is_internal' => $isInternal ? 1 : 0,
        ]);

        $commentId = (int)$this->pdo->lastInsertId();
        $this->audit->log((int)$user['id'], 'ticket.comment_added', 'ticket', (int)$ticket['id'], ['comment_id' => $commentId]);

        Response::success(['comment_id' => $commentId], 'Komentář byl přidán.', 201);
    }

    private function validateTicketInput(string $title, string $description, string $priority, int $categoryId): array
    {
        $errors = [];

        if (!Validator::length($title, 5, 160)) {
            $errors['title'] = 'Název musí mít 5 až 160 znaků.';
        }

        if (!Validator::length($description, 10, 5000)) {
            $errors['description'] = 'Popis musí mít 10 až 5000 znaků.';
        }

        if (!Validator::enum($priority, self::PRIORITIES)) {
            $errors['priority'] = 'Neplatná priorita.';
        }

        if (!$this->categoryExists($categoryId)) {
            $errors['category_id'] = 'Vybraná kategorie neexistuje.';
        }

        return $errors;
    }

    private function loadTicket(int $id): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, c.name AS category_name, c.color AS category_color,
                    author.name AS author_name, author.email AS author_email,
                    assigned.name AS assigned_name
             FROM tickets t
             JOIN categories c ON c.id = t.category_id
             JOIN users author ON author.id = t.author_id
             LEFT JOIN users assigned ON assigned.id = t.assigned_to
             WHERE t.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            throw new HttpException(404, 'Požadavek nebyl nalezen.');
        }

        return $ticket;
    }

    private function assertCanView(array $ticket, array $user): void
    {
        if ($this->auth->canManageTickets($user)) {
            return;
        }

        if ((int)$ticket['author_id'] === (int)$user['id']) {
            return;
        }

        throw new HttpException(403, 'K tomuto požadavku nemáte oprávnění.');
    }

    private function categoryExists(int $categoryId): bool
    {
        if ($categoryId < 1) {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = :id AND is_active = 1');
        $stmt->execute(['id' => $categoryId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function isTechnicianOrAdmin(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id AND u.is_active = 1 AND r.name IN ("TECHNICIAN", "ADMIN")'
        );
        $stmt->execute(['id' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}

