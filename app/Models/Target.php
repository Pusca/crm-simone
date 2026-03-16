<?php

declare(strict_types=1);

namespace App\Models;

final class Target extends BaseModel
{
    public static function currentForDate(int $userId, string $period, string $metric, string $date): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM targets
             WHERE user_id = :user_id
               AND period = :period
               AND metric = :metric
               AND :target_date BETWEEN start_date AND end_date
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'period' => $period,
            'metric' => $metric,
            'target_date' => $date,
        ]);
        $target = $stmt->fetch();
        return $target ?: null;
    }

    public static function createMany(array $rows): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO targets (user_id, period, metric, target_value, start_date, end_date, created_at)
             VALUES (:user_id, :period, :metric, :target_value, :start_date, :end_date, NOW())'
        );
        foreach ($rows as $row) {
            $stmt->execute([
                'user_id' => (int) $row['user_id'],
                'period' => $row['period'],
                'metric' => $row['metric'],
                'target_value' => (int) $row['target_value'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
            ]);
        }
    }

    public static function list(array $filters = []): array
    {
        $where = [];
        $params = [];
        if (($filters['user_id'] ?? '') !== '') {
            $where[] = 't.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }
        if (($filters['period'] ?? '') !== '') {
            $where[] = 't.period = :period';
            $params['period'] = $filters['period'];
        }
        $sql = 'SELECT t.*, u.name AS seller_name
                FROM targets t
                JOIN users u ON u.id = t.user_id';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY t.start_date DESC, t.user_id ASC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

