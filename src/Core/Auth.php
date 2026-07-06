<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Auth
{
    private PDO $pdo;
    private ?array $currentUser = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $this->currentUser = null;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public function user(): ?array
    {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }

        $userId = $_SESSION['user_id'] ?? null;

        if ($userId === null) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.name, u.email, u.phone, u.department, u.is_active, u.created_at,
                    r.name AS role_name, r.label AS role_label
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id AND u.is_active = 1'
        );
        $stmt->execute(['id' => (int)$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            unset($_SESSION['user_id']);
            return null;
        }

        $this->currentUser = $this->sanitizeUser($user);
        return $this->currentUser;
    }

    public function requireLogin(): array
    {
        $user = $this->user();

        if ($user === null) {
            throw new HttpException(401, 'Pro tuto operaci je nutné přihlášení.');
        }

        return $user;
    }

    public function requireAnyRole(array $roles): array
    {
        $user = $this->requireLogin();

        if (!$this->hasAnyRole($user, $roles)) {
            throw new HttpException(403, 'Nemáte oprávnění pro tuto operaci.');
        }

        return $user;
    }

    public function hasAnyRole(array $user, array $roles): bool
    {
        return in_array($user['role_name'], $roles, true);
    }

    public function canManageTickets(array $user): bool
    {
        return $this->hasAnyRole($user, ['TECHNICIAN', 'ADMIN']);
    }

    public function clearCache(): void
    {
        $this->currentUser = null;
    }

    private function sanitizeUser(array $user): array
    {
        return [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'department' => $user['department'],
            'is_active' => (bool)$user['is_active'],
            'role_name' => $user['role_name'],
            'role_label' => $user['role_label'],
            'created_at' => $user['created_at'],
        ];
    }
}
