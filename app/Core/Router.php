<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /**
     * @var array<int, array{method:string,pattern:string,handler:mixed,middleware:array}>
     */
    private array $routes = [];

    public function get(string $pattern, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function put(string $pattern, mixed $handler, array $middleware = []): void
    {
        $this->add('PUT', $pattern, $handler, $middleware);
    }

    public function delete(string $pattern, mixed $handler, array $middleware = []): void
    {
        $this->add('DELETE', $pattern, $handler, $middleware);
    }

    private function add(string $method, string $pattern, mixed $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        $cleanUri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $regex = $this->toRegex($route['pattern']);
            if (!preg_match($regex, $cleanUri, $matches)) {
                continue;
            }

            $this->runMiddleware($route['middleware']);

            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[$key] = $value;
                }
            }

            $handler = $route['handler'];
            if (is_callable($handler)) {
                call_user_func_array($handler, $params);
                return;
            }

            if (is_array($handler) && count($handler) === 2) {
                [$class, $action] = $handler;
                $instance = new $class();
                call_user_func_array([$instance, $action], $params);
                return;
            }
        }

        http_response_code(404);
        echo 'Pagina non trovata.';
    }

    private function toRegex(string $pattern): string
    {
        $clean = rtrim($pattern, '/') ?: '/';
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $clean);
        return '#^' . $regex . '$#';
    }

    private function runMiddleware(array $middleware): void
    {
        if (($middleware['auth'] ?? false) && !Auth::check()) {
            set_flash('error', 'Effettua il login per continuare.');
            header('Location: ' . base_url('login'));
            exit;
        }

        if (($middleware['role'] ?? null) === 'manager' && !Auth::isManager()) {
            http_response_code(403);
            echo 'Accesso negato.';
            exit;
        }
    }
}

