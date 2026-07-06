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

class AdminController
{
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

    public function dashboard(): void
    {
        $this->auth->requireAnyRole(['TECHNICIAN', 'ADMIN']);

        $statusCounts = $this->pdo->query(
            'SELECT status, COUNT(*) AS total
             FROM tickets
             GROUP BY status
             ORDER BY status'
        )->fetchAll();

        $priorityCounts = $this->pdo->query(
            'SELECT priority, COUNT(*) AS total
             FROM tickets
             GROUP BY priority
             ORDER BY FIELD(priority, "urgent", "high", "medium", "low")'
        )->fetchAll();

        $categoryCounts = $this->pdo->query(
            'SELECT c.name, c.color, COUNT(t.id) AS total
             FROM categories c
             LEFT JOIN tickets t ON t.category_id = c.id
             GROUP BY c.id, c.name, c.color
             ORDER BY total DESC, c.name'
        )->fetchAll();

        $summary = $this->pdo->query(
            'SELECT
                COUNT(*) AS total_tickets,
                SUM(status IN ("new", "open", "in_progress", "waiting_for_user")) AS active_tickets,
                SUM(status = "resolved") AS resolved_tickets,
                SUM(status = "closed") AS closed_tickets
             FROM tickets'
        )->fetch();

        $latestTickets = $this->pdo->query(
            'SELECT t.id, t.title, t.status, t.priority, t.created_at, u.name AS author_name
             FROM tickets t
             JOIN users u ON u.id = t.author_id
             ORDER BY t.created_at DESC
             LIMIT 6'
        )->fetchAll();

        $latestAudit = $this->pdo->query(
            'SELECT a.action, a.entity_type, a.entity_id, a.created_at, u.name AS actor_name
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.actor_id
             ORDER BY a.created_at DESC
             LIMIT 8'
        )->fetchAll();

        Response::success([
            'summary' => $summary,
            'status_counts' => $statusCounts,
            'priority_counts' => $priorityCounts,
            'category_counts' => $categoryCounts,
            'latest_tickets' => $latestTickets,
            'latest_audit' => $latestAudit,
        ]);
    }

    public function users(): void
    {
        $this->auth->requireAnyRole(['ADMIN']);
        $stmt = $this->pdo->query(
            'SELECT u.id, u.name, u.email, u.phone, u.department, u.is_active, u.created_at,
                    r.id AS role_id, r.name AS role_name, r.label AS role_label
             FROM users u
             JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC'
        );

        Response::success(['items' => $stmt->fetchAll()]);
    }

    public function roles(): void
    {
        $this->auth->requireAnyRole(['ADMIN']);
        $stmt = $this->pdo->query('SELECT id, name, label FROM roles ORDER BY id');
        Response::success(['items' => $stmt->fetchAll()]);
    }

    public function createUser(Request $request): void
    {
        $currentUser = $this->auth->requireAnyRole(['ADMIN']);
        $this->csrf->assertValid($request);
        $data = $request->json();

        $name = Validator::cleanString($data['name'] ?? '');
        $email = mb_strtolower(Validator::cleanString($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $roleId = (int)($data['role_id'] ?? 0);
        $phone = Validator::cleanString($data['phone'] ?? '');
        $department = Validator::cleanString($data['department'] ?? '');
        $isActive = array_key_exists('is_active', $data) ? (bool)$data['is_active'] : true;
        $errors = [];

        if (!Validator::length($name, 2, 120)) {
            $errors['name'] = 'Jméno musí mít 2 až 120 znaků.';
        }

        if (!Validator::email($email)) {
            $errors['email'] = 'Zadejte platný e-mail.';
        }

        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Heslo musí mít alespoň 8 znaků.';
        }

        if (!$this->roleExists($roleId)) {
            $errors['role_id'] = 'Vybraná role neexistuje.';
        }

        if (mb_strlen($phone) > 40) {
            $errors['phone'] = 'Telefon může mít nejvýše 40 znaků.';
        }

        if (mb_strlen($department) > 120) {
            $errors['department'] = 'Oddělení může mít nejvýše 120 znaků.';
        }

        if ($errors !== []) {
            throw new HttpException(422, 'Uživatel obsahuje chyby.', $errors);
        }

        $duplicate = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $duplicate->execute(['email' => $email]);

        if ((int)$duplicate->fetchColumn() > 0) {
            throw new HttpException(409, 'Uživatel s tímto e-mailem už existuje.', ['email' => 'E-mail už je obsazený.']);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (role_id, name, email, password_hash, phone, department, is_active)
             VALUES (:role_id, :name, :email, :password_hash, :phone, :department, :is_active)'
        );
        $stmt->execute([
            'role_id' => $roleId,
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => $phone === '' ? null : $phone,
            'department' => $department === '' ? null : $department,
            'is_active' => $isActive ? 1 : 0,
        ]);

        $userId = (int)$this->pdo->lastInsertId();
        $this->audit->log((int)$currentUser['id'], 'admin.user_created', 'user', $userId);

        Response::success(['user' => $this->findUser($userId)], 'Uživatel byl vytvořen.', 201);
    }

    public function updateUser(Request $request, array $params): void
    {
        $currentUser = $this->auth->requireAnyRole(['ADMIN']);
        $this->csrf->assertValid($request);
        $userId = (int)$params['id'];
        $data = $request->json();
        $existing = $this->findUser($userId);

        if (!$existing) {
            throw new HttpException(404, 'Uživatel nebyl nalezen.');
        }

        $name = array_key_exists('name', $data) ? Validator::cleanString($data['name']) : $existing['name'];
        $roleId = array_key_exists('role_id', $data) ? (int)$data['role_id'] : (int)$existing['role_id'];
        $isActive = array_key_exists('is_active', $data) ? (bool)$data['is_active'] : (bool)$existing['is_active'];
        $errors = [];

        if (!Validator::length($name, 2, 120)) {
            $errors['name'] = 'Jméno musí mít 2 až 120 znaků.';
        }

        if (!$this->roleExists($roleId)) {
            $errors['role_id'] = 'Vybraná role neexistuje.';
        }

        if ((int)$currentUser['id'] === $userId && !$isActive) {
            $errors['is_active'] = 'Administrátor nemůže deaktivovat sám sebe.';
        }

        if ($errors !== []) {
            throw new HttpException(422, 'Uživatel obsahuje chyby.', $errors);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET name = :name, role_id = :role_id, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute([
            'name' => $name,
            'role_id' => $roleId,
            'is_active' => $isActive ? 1 : 0,
            'id' => $userId,
        ]);

        $this->audit->log((int)$currentUser['id'], 'admin.user_updated', 'user', $userId);
        Response::success(['user' => $this->findUser($userId)], 'Uživatel byl uložen.');
    }

    public function categories(): void
    {
        $this->auth->requireAnyRole(['ADMIN']);
        $stmt = $this->pdo->query(
            'SELECT id, name, description, color, is_active, created_at
             FROM categories
             ORDER BY name'
        );

        Response::success(['items' => $stmt->fetchAll()]);
    }

    public function createCategory(Request $request): void
    {
        $user = $this->auth->requireAnyRole(['ADMIN']);
        $this->csrf->assertValid($request);
        $data = $request->json();
        $category = $this->validateCategory($data);

        $stmt = $this->pdo->prepare(
            'INSERT INTO categories (name, description, color, is_active)
             VALUES (:name, :description, :color, :is_active)'
        );
        $stmt->execute($category);
        $categoryId = (int)$this->pdo->lastInsertId();

        $this->audit->log((int)$user['id'], 'admin.category_created', 'category', $categoryId);
        Response::success(['category_id' => $categoryId], 'Kategorie byla vytvořena.', 201);
    }

    public function updateCategory(Request $request, array $params): void
    {
        $user = $this->auth->requireAnyRole(['ADMIN']);
        $this->csrf->assertValid($request);
        $categoryId = (int)$params['id'];
        $data = $request->json();
        $existing = $this->findCategory($categoryId);

        if (!$existing) {
            throw new HttpException(404, 'Kategorie nebyla nalezena.');
        }

        $category = $this->validateCategory(array_merge($existing, $data));
        $category['id'] = $categoryId;
        $stmt = $this->pdo->prepare(
            'UPDATE categories
             SET name = :name, description = :description, color = :color, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute($category);

        $this->audit->log((int)$user['id'], 'admin.category_updated', 'category', $categoryId);
        Response::success(['category' => $this->findCategory($categoryId)], 'Kategorie byla uložena.');
    }

    public function auditLog(): void
    {
        $this->auth->requireAnyRole(['ADMIN']);
        $stmt = $this->pdo->query(
            'SELECT a.id, a.action, a.entity_type, a.entity_id, a.metadata, a.ip_address, a.created_at,
                    u.name AS actor_name
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.actor_id
             ORDER BY a.created_at DESC
             LIMIT 80'
        );

        Response::success(['items' => $stmt->fetchAll()]);
    }

    private function validateCategory(array $data): array
    {
        $name = Validator::cleanString($data['name'] ?? '');
        $description = Validator::cleanString($data['description'] ?? '');
        $color = Validator::cleanString($data['color'] ?? '#2563eb');
        $isActive = array_key_exists('is_active', $data) ? (bool)$data['is_active'] : true;
        $errors = [];

        if (!Validator::length($name, 2, 100)) {
            $errors['name'] = 'Název kategorie musí mít 2 až 100 znaků.';
        }

        if (mb_strlen($description) > 255) {
            $errors['description'] = 'Popis může mít nejvýše 255 znaků.';
        }

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $errors['color'] = 'Barva musí být ve formátu #RRGGBB.';
        }

        if ($errors !== []) {
            throw new HttpException(422, 'Kategorie obsahuje chyby.', $errors);
        }

        return [
            'name' => $name,
            'description' => $description === '' ? null : $description,
            'color' => $color,
            'is_active' => $isActive ? 1 : 0,
        ];
    }

    private function findUser(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.name, u.email, u.phone, u.department, u.is_active,
                    r.id AS role_id, r.name AS role_name, r.label AS role_label
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    private function findCategory(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, description, color, is_active FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    private function roleExists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM roles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
