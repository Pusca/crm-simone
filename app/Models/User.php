<?php

declare(strict_types=1);

namespace App\Models;

final class User extends BaseModel
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function all(): array
    {
        $stmt = self::db()->query('SELECT id, name, email, role, created_at FROM users ORDER BY role DESC, name ASC');
        return $stmt->fetchAll();
    }

    public static function sellers(): array
    {
        $stmt = self::db()->query("SELECT id, name, email, role FROM users WHERE role IN ('seller','manager') ORDER BY role DESC, name ASC");
        return $stmt->fetchAll();
    }

    public static function create(string $name, string $email, string $password, string $role): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO users (name, email, password_hash, role, created_at) VALUES (:name, :email, :password_hash, :role, NOW())'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
        return (int) self::db()->lastInsertId();
    }
}

