<?php

declare(strict_types=1);

namespace App\Models;

final class QuoteCategory extends BaseModel
{
    public static function all(): array
    {
        $stmt = self::db()->query('SELECT * FROM quote_categories ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function active(): array
    {
        $stmt = self::db()->query('SELECT * FROM quote_categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO quote_categories (name, sort_order, is_active, created_at)
             VALUES (:name, :sort_order, :is_active, NOW())'
        );
        $stmt->execute([
            'name' => $data['name'],
            'sort_order' => (int) $data['sort_order'],
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE quote_categories
             SET name = :name,
                 sort_order = :sort_order,
                 is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'sort_order' => (int) $data['sort_order'],
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = self::db()->prepare('DELETE FROM quote_categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

