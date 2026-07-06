<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\TicketController;
use App\Core\AuditLogger;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

$config = require __DIR__ . '/../../src/bootstrap.php';

try {
    $database = new Database($config['database']);
    $pdo = $database->pdo();
    $auth = new Auth($pdo);
    $csrf = new Csrf();
    $audit = new AuditLogger($pdo);

    $authController = new AuthController($pdo, $auth, $csrf, $audit);
    $ticketController = new TicketController($pdo, $auth, $csrf, $audit);
    $adminController = new AdminController($pdo, $auth, $csrf, $audit);

    $router = new Router();

    $router->add('GET', '/health', fn (Request $request, array $params) => Response::success(['status' => 'ok'], 'API je dostupné.'));

    $router->add('POST', '/auth/register', [$authController, 'register']);
    $router->add('POST', '/auth/login', [$authController, 'login']);
    $router->add('POST', '/auth/logout', [$authController, 'logout']);
    $router->add('GET', '/auth/me', [$authController, 'me']);
    $router->add('PATCH', '/profile', [$authController, 'updateProfile']);

    $router->add('GET', '/categories', [$ticketController, 'categories']);
    $router->add('GET', '/tickets', [$ticketController, 'index']);
    $router->add('POST', '/tickets', [$ticketController, 'create']);
    $router->add('GET', '/tickets/{id}', [$ticketController, 'show']);
    $router->add('GET', '/tickets/{id}/kb', [$ticketController, 'knowledgeBasePdf']);
    $router->add('PATCH', '/tickets/{id}', [$ticketController, 'update']);
    $router->add('PUT', '/tickets/{id}', [$ticketController, 'update']);
    $router->add('DELETE', '/tickets/{id}', [$ticketController, 'delete']);
    $router->add('POST', '/tickets/{id}/comments', [$ticketController, 'addComment']);

    $router->add('GET', '/admin/dashboard', [$adminController, 'dashboard']);
    $router->add('GET', '/admin/users', [$adminController, 'users']);
    $router->add('POST', '/admin/users', [$adminController, 'createUser']);
    $router->add('PATCH', '/admin/users/{id}', [$adminController, 'updateUser']);
    $router->add('GET', '/admin/roles', [$adminController, 'roles']);
    $router->add('GET', '/admin/categories', [$adminController, 'categories']);
    $router->add('POST', '/admin/categories', [$adminController, 'createCategory']);
    $router->add('PATCH', '/admin/categories/{id}', [$adminController, 'updateCategory']);
    $router->add('GET', '/admin/audit-log', [$adminController, 'auditLog']);

    $router->dispatch(Request::fromGlobals());
} catch (HttpException $exception) {
    Response::error($exception->getMessage(), $exception->getStatusCode(), $exception->getErrors());
} catch (Throwable $exception) {
    error_log($exception);
    $debug = (bool)($config['app']['debug'] ?? false);
    Response::error($debug ? $exception->getMessage() : 'Nastala neočekávaná chyba serveru.', 500);
}
