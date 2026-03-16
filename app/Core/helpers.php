<?php

declare(strict_types=1);

function config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $baseConfigPath = __DIR__ . '/../../config/config.php';
        $localConfigPath = __DIR__ . '/../../config/config.local.php';

        $config = require $baseConfigPath;

        if (file_exists($localConfigPath)) {
            $localConfig = require $localConfigPath;
            if (is_array($localConfig)) {
                $config = array_replace_recursive($config, $localConfig);
            }
        }
    }

    if ($key === null) {
        return $config;
    }

    $segments = explode('.', $key);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function base_url(string $path = ''): string
{
    $base = rtrim((string) config('app.base_url', '/'), '/');
    $path = ltrim($path, '/');
    if ($base === '') {
        $base = '/';
    }

    if ($base === '/') {
        return '/' . $path;
    }

    return $base . ($path !== '' ? '/' . $path : '');
}

function e(null|string|int|float $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function set_flash(string $key, string $value): void
{
    $_SESSION['_flash_next'][$key] = $value;
}

function flash(string $key): ?string
{
    $messages = $GLOBALS['_flash_messages'] ?? [];
    return $messages[$key] ?? null;
}

function with_old_input(array $input): void
{
    $_SESSION['_old_next'] = $input;
}

function old(string $key, mixed $default = ''): mixed
{
    $old = $GLOBALS['_old_input'] ?? [];
    return $old[$key] ?? $default;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    if (empty($_SESSION['_csrf_token']) || $token === null) {
        return false;
    }
    return hash_equals($_SESSION['_csrf_token'], $token);
}

function is_active_path(string $path): bool
{
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return str_starts_with($current, $path);
}

function request_method(): string
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if ($method === 'POST' && isset($_POST['_method'])) {
        $override = strtoupper((string) $_POST['_method']);
        if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
            return $override;
        }
    }
    return $method;
}

function selected(string|int|null $value, string|int|null $expected): string
{
    return (string) $value === (string) $expected ? 'selected' : '';
}