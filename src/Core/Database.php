<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private array $config;
    private ?PDO $pdo = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pdo(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->config['host'],
            (int)$this->config['port'],
            $this->config['name'],
            $this->config['charset']
        );

        $this->pdo = new PDO($dsn, $this->config['user'], $this->config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $this->pdo;
    }
}

