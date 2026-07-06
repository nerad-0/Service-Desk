<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public function assertValid(Request $request): void
    {
        $expected = $_SESSION['csrf_token'] ?? '';
        $given = $request->header('X-CSRF-Token') ?? '';

        if ($expected === '' || $given === '' || !hash_equals($expected, $given)) {
            throw new HttpException(403, 'Neplatný bezpečnostní token požadavku.');
        }
    }
}

