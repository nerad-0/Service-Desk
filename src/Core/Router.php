<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'regex' => $this->compilePattern($pattern),
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        if ($request->method() === 'OPTIONS') {
            Response::success([], 'OK');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (preg_match($route['regex'], $request->path(), $matches)) {
                $params = [];

                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                call_user_func($route['handler'], $request, $params);
                return;
            }
        }

        throw new HttpException(404, 'Endpoint nebyl nalezen.');
    }

    private function compilePattern(string $pattern): string
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }
}

