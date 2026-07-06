<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $headers;
    private ?array $jsonBody = null;

    private function __construct(string $method, string $path, array $query, array $headers)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->query = $query;
        $this->headers = $headers;
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $route = $_GET['route'] ?? null;

        if ($route === null || $route === '') {
            $route = $_SERVER['PATH_INFO'] ?? '';
        }

        $path = '/' . trim((string)$route, '/');

        if ($path === '/') {
            $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

            if ($scriptName !== '' && str_starts_with($uriPath, $scriptName)) {
                $path = '/' . trim(substr($uriPath, strlen($scriptName)), '/');
            }
        }

        return new self($method, $path === '/' ? '/' : $path, $_GET, self::collectHeaders());
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function json(): array
    {
        if ($this->jsonBody !== null) {
            return $this->jsonBody;
        }

        $raw = file_get_contents('php://input');

        if ($raw === false || trim($raw) === '') {
            $this->jsonBody = [];
            return $this->jsonBody;
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            throw new HttpException(400, 'Požadavek neobsahuje platný JSON.');
        }

        $this->jsonBody = $decoded;
        return $this->jsonBody;
    }

    public function header(string $name): ?string
    {
        $key = strtolower($name);
        return $this->headers[$key] ?? null;
    }

    private static function collectHeaders(): array
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[strtolower($name)] = $value;
            }
            return $headers;
        }

        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headerName = strtolower(str_replace('_', '-', substr($name, 5)));
                $headers[$headerName] = (string)$value;
            }
        }

        return $headers;
    }
}

