<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $user = null;

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        self::login((int) $user['id']);
        return true;
    }

    public static function login(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
        self::$user = null;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        self::$user = null;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$user !== null) {
            return self::$user;
        }

        $user = User::find((int) $_SESSION['user_id']);
        self::$user = $user ?: null;
        return self::$user;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    public static function isManager(): bool
    {
        return self::role() === 'manager';
    }

    public static function canAccessUserData(int $ownerUserId): bool
    {
        if (self::isManager()) {
            return true;
        }
        return self::id() === $ownerUserId;
    }
}

