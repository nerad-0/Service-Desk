<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

$configFile = __DIR__ . '/../config/config.php';

if (!is_file($configFile)) {
    $configFile = __DIR__ . '/../config/config.example.php';
}

$config = require $configFile;
$debug = (bool)($config['app']['debug'] ?? false);

error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
date_default_timezone_set('Europe/Prague');

$isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

session_name((string)($config['app']['session_name'] ?? 'SERVISDESK_SESSION'));
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

return $config;

