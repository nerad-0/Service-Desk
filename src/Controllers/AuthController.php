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

class AuthController
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

    public function register(Request $request): void
    {
        throw new HttpException(403, 'Veřejná registrace je vypnutá. Účty vytváří administrátor.');
    }

    public function login(Request $request): void
    {
        $data = $request->json();
        $email = mb_strtolower(Validator::cleanString($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');

        if (!Validator::email($email) || $password === '') {
            throw new HttpException(422, 'Zadejte platný e-mail a heslo.');
        }

        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.password_hash
             FROM users u
             WHERE u.email = :email AND u.is_active = 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new HttpException(401, 'Neplatný e-mail nebo heslo.');
        }

        $this->auth->login((int)$user['id']);
        $currentUser = $this->auth->user();
        $this->audit->log((int)$user['id'], 'auth.logged_in', 'user', (int)$user['id']);

        Response::success([
            'user' => $currentUser,
            'csrf_token' => $this->csrf->token(),
        ], 'Přihlášení bylo úspěšné.');
    }

    public function logout(Request $request): void
    {
        $user = $this->auth->requireLogin();
        $this->csrf->assertValid($request);
        $this->audit->log((int)$user['id'], 'auth.logged_out', 'user', (int)$user['id']);
        $this->auth->logout();

        Response::success([], 'Odhlášení bylo úspěšné.');
    }

    public function me(): void
    {
        Response::success([
            'user' => $this->auth->user(),
            'csrf_token' => $this->csrf->token(),
        ]);
    }

    public function updateProfile(Request $request): void
    {
        $user = $this->auth->requireLogin();
        $this->csrf->assertValid($request);
        $data = $request->json();

        $name = Validator::cleanString($data['name'] ?? $user['name']);
        $phone = Validator::cleanString($data['phone'] ?? '');
        $department = Validator::cleanString($data['department'] ?? '');
        $errors = [];

        if (!Validator::length($name, 2, 120)) {
            $errors['name'] = 'Jméno musí mít 2 až 120 znaků.';
        }

        if (mb_strlen($phone) > 40) {
            $errors['phone'] = 'Telefon může mít nejvýše 40 znaků.';
        }

        if (mb_strlen($department) > 120) {
            $errors['department'] = 'Oddělení může mít nejvýše 120 znaků.';
        }

        $newPassword = (string)($data['new_password'] ?? '');

        if ($newPassword !== '' && mb_strlen($newPassword) < 8) {
            $errors['new_password'] = 'Nové heslo musí mít alespoň 8 znaků.';
        }

        if ($errors !== []) {
            throw new HttpException(422, 'Profil obsahuje chyby.', $errors);
        }

        if ($newPassword !== '') {
            $currentPassword = (string)($data['current_password'] ?? '');
            $stmt = $this->pdo->prepare('SELECT password_hash FROM users WHERE id = :id');
            $stmt->execute(['id' => (int)$user['id']]);
            $hash = $stmt->fetchColumn();

            if (!$hash || !password_verify($currentPassword, (string)$hash)) {
                throw new HttpException(422, 'Současné heslo není správné.', ['current_password' => 'Zadejte správné současné heslo.']);
            }

            $update = $this->pdo->prepare(
                'UPDATE users
                 SET name = :name, phone = :phone, department = :department, password_hash = :password_hash
                 WHERE id = :id'
            );
            $update->execute([
                'name' => $name,
                'phone' => $phone === '' ? null : $phone,
                'department' => $department === '' ? null : $department,
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'id' => (int)$user['id'],
            ]);
        } else {
            $update = $this->pdo->prepare(
                'UPDATE users
                 SET name = :name, phone = :phone, department = :department
                 WHERE id = :id'
            );
            $update->execute([
                'name' => $name,
                'phone' => $phone === '' ? null : $phone,
                'department' => $department === '' ? null : $department,
                'id' => (int)$user['id'],
            ]);
        }

        $this->audit->log((int)$user['id'], 'profile.updated', 'user', (int)$user['id']);
        $this->auth->clearCache();

        Response::success([
            'user' => $this->auth->user(),
        ], 'Profil byl uložen.');
    }
}
