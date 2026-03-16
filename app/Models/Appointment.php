<?php

declare(strict_types=1);

namespace App\Models;

final class Appointment extends BaseModel
{
    public static function byWeek(string $weekStart, string $weekEnd, array $currentUser): array
    {
        $sql = 'SELECT ap.*, c.company_name, u.name AS seller_name, ps.name AS stage_name
                FROM appointments ap
                JOIN clients c ON c.id = ap.client_id
                JOIN users u ON u.id = ap.user_id
                LEFT JOIN pipeline_stages ps ON ps.id = ap.stage_id
                WHERE ap.start_at BETWEEN :start_at AND :end_at';
        $params = [
            'start_at' => $weekStart . ' 00:00:00',
            'end_at' => $weekEnd . ' 23:59:59',
        ];

        if ($currentUser['role'] === 'seller') {
            $sql .= ' AND ap.user_id = :user_id';
            $params['user_id'] = (int) $currentUser['id'];
        }

        $sql .= ' ORDER BY ap.start_at ASC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findAccessible(int $id, array $currentUser): ?array
    {
        $sql = 'SELECT * FROM appointments WHERE id = :id';
        $params = ['id' => $id];
        if ($currentUser['role'] === 'seller') {
            $sql .= ' AND user_id = :user_id';
            $params['user_id'] = (int) $currentUser['id'];
        }
        $sql .= ' LIMIT 1';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO appointments
            (client_id, user_id, title, description, start_at, end_at, location, stage_id, created_at)
            VALUES
            (:client_id, :user_id, :title, :description, :start_at, :end_at, :location, :stage_id, NOW())'
        );
        $stmt->execute([
            'client_id' => (int) $data['client_id'],
            'user_id' => (int) $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'location' => $data['location'] ?: null,
            'stage_id' => $data['stage_id'] !== '' ? (int) $data['stage_id'] : null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE appointments
             SET client_id = :client_id,
                 title = :title,
                 description = :description,
                 start_at = :start_at,
                 end_at = :end_at,
                 location = :location,
                 stage_id = :stage_id
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'client_id' => (int) $data['client_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'location' => $data['location'] ?: null,
            'stage_id' => $data['stage_id'] !== '' ? (int) $data['stage_id'] : null,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = self::db()->prepare('DELETE FROM appointments WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function countBetween(array $currentUser, string $from, string $to): int
    {
        $sql = 'SELECT COUNT(*) FROM appointments WHERE start_at BETWEEN :from_date AND :to_date';
        $params = [
            'from_date' => $from . ' 00:00:00',
            'to_date' => $to . ' 23:59:59',
        ];
        if ($currentUser['role'] === 'seller') {
            $sql .= ' AND user_id = :user_id';
            $params['user_id'] = (int) $currentUser['id'];
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function dailyCounts(int $userId, string $from, string $to): array
    {
        $stmt = self::db()->prepare(
            "SELECT DATE(start_at) AS d, COUNT(*) AS total
             FROM appointments
             WHERE user_id = :user_id
               AND start_at BETWEEN :from_date AND :to_date
             GROUP BY DATE(start_at)"
        );
        $stmt->execute([
            'user_id' => $userId,
            'from_date' => $from . ' 00:00:00',
            'to_date' => $to . ' 23:59:59',
        ]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['d']] = (int) $row['total'];
        }
        return $map;
    }
}

