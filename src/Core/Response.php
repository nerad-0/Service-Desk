<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function success(array $data = [], string $message = 'OK', int $status = 200): void
    {
        self::send([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ], $status);
    }

    public static function error(string $message, int $status = 400, array $errors = []): void
    {
        self::send([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $status);
    }

    private static function send(array $payload, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

