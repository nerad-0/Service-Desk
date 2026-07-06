<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class HttpException extends RuntimeException
{
    private int $statusCode;
    private array $errors;

    public function __construct(int $statusCode, string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

