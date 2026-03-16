<?php

declare(strict_types=1);

namespace App\Models;

final class Activity extends BaseModel
{
    public static function search(array $filters, array $currentUser): array
    {
        $where = [];
        $params = [];

        if (($filters['type'] ?? '') !== '') {
            $where[] = 'a.type = :type';
            $params['type'] = $filters['type'];
        }

        if (($filters['stage_id'] ?? '') !== '') {
            $where[] = 'a.stage_id = :stage_id';
            $params['stage_id'] = (int) $filters['stage_id'];
        }

        if (($filters['client_id'] ?? '') !== '') {
            $where[] = 'a.client_id = :client_id';
            $params['client_id'] = (int) $filters['client_id'];
        }

        if (($filters['date_from'] ?? '') !== '') {
            $where[] = 'a.occurred_at >= :date_from';
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (($filters['date_to'] ?? '') !== '') {
            $where[] = 'a.occurred_at <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if ($currentUser['role'] === 'seller') {
            $where[] = 'a.user_id = :current_user_id';
            $params['current_user_id'] = (int) $currentUser['id'];
        }

        $sql = 'SELECT a.*, c.company_name, u.name AS seller_name, ps.name AS stage_name
                FROM activities a
                JOIN clients c ON c.id = a.client_id
                JOIN users u ON u.id = a.user_id
                LEFT JOIN pipeline_stages ps ON ps.id = a.stage_id';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY a.occurred_at DESC, a.id DESC';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findAccessible(int $id, array $currentUser): ?array
    {
        $sql = 'SELECT * FROM activities WHERE id = :id';
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
            'INSERT INTO activities
            (client_id, user_id, type, stage_id, subject, body, occurred_at, next_action_at, created_at)
            VALUES
            (:client_id, :user_id, :type, :stage_id, :subject, :body, :occurred_at, :next_action_at, NOW())'
        );
        $stmt->execute([
            'client_id' => (int) $data['client_id'],
            'user_id' => (int) $data['user_id'],
            'type' => $data['type'],
            'stage_id' => $data['stage_id'] !== '' ? (int) $data['stage_id'] : null,
            'subject' => $data['subject'],
            'body' => $data['body'] ?: null,
            'occurred_at' => $data['occurred_at'],
            'next_action_at' => $data['next_action_at'] !== '' ? $data['next_action_at'] : null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE activities
             SET client_id = :client_id,
                 type = :type,
                 stage_id = :stage_id,
                 subject = :subject,
                 body = :body,
                 occurred_at = :occurred_at,
                 next_action_at = :next_action_at
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'client_id' => (int) $data['client_id'],
            'type' => $data['type'],
            'stage_id' => $data['stage_id'] !== '' ? (int) $data['stage_id'] : null,
            'subject' => $data['subject'],
            'body' => $data['body'] ?: null,
            'occurred_at' => $data['occurred_at'],
            'next_action_at' => $data['next_action_at'] !== '' ? $data['next_action_at'] : null,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = self::db()->prepare('DELETE FROM activities WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function dailyCounts(int $userId, string $from, string $to): array
    {
        $stmt = self::db()->prepare(
            "SELECT DATE(occurred_at) AS d, COUNT(*) AS total
             FROM activities
             WHERE user_id = :user_id
               AND type = 'meeting'
               AND occurred_at BETWEEN :from_date AND :to_date
             GROUP BY DATE(occurred_at)"
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

