<?php

declare(strict_types=1);

namespace App\Core;

use Exception;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$view}");
        }

        $user = Auth::user();
        extract($data, EXTR_SKIP);
        $contentView = $viewFile;
        require __DIR__ . '/../views/layouts/main.php';
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . base_url($path));
        exit;
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            set_flash('error', 'Sessione scaduta. Effettua il login.');
            $this->redirect('login');
        }
    }

    protected function requireManager(): void
    {
        $this->requireAuth();
        if (!Auth::isManager()) {
            http_response_code(403);
            echo 'Accesso negato.';
            exit;
        }
    }

    protected function verifyCsrfOrFail(): void
    {
        if (!verify_csrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Token CSRF non valido.';
            exit;
        }
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}

