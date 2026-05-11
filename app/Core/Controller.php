<?php

declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;
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

    protected function parseDateInput(string $value): ?DateTimeImmutable
    {
        return $this->parseDateUsingFormats($value, ['Y-m-d']);
    }

    protected function normalizeDateInput(string $value): string
    {
        $date = $this->parseDateInput($value);
        return $date ? $date->format('Y-m-d') : trim($value);
    }

    protected function parseDateTimeInput(string $value): ?DateTimeImmutable
    {
        return $this->parseDateUsingFormats($value, ['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i']);
    }

    protected function normalizeDateTimeInput(string $value): string
    {
        $date = $this->parseDateTimeInput($value);
        return $date ? $date->format('Y-m-d H:i:s') : trim($value);
    }

    /**
     * @param array<int, string> $formats
     */
    private function parseDateUsingFormats(string $value, array $formats): ?DateTimeImmutable
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            $hasErrors = is_array($errors)
                && ((int) $errors['warning_count'] > 0 || (int) $errors['error_count'] > 0);

            if ($date instanceof DateTimeImmutable && !$hasErrors && $date->format($format) === $value) {
                return $date;
            }
        }

        return null;
    }
}
