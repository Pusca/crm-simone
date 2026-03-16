<?php

declare(strict_types=1);

namespace App\Models;

final class Sale extends BaseModel
{
    public static function search(array $filters, array $currentUser): array
    {
        $where = [];
        $params = [];

        if (($filters['client_id'] ?? '') !== '') {
            $where[] = 's.client_id = :client_id';
            $params['client_id'] = (int) $filters['client_id'];
        }

        if (($filters['date_from'] ?? '') !== '') {
            $where[] = 's.closed_at >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (($filters['date_to'] ?? '') !== '') {
            $where[] = 's.closed_at <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (($filters['q'] ?? '') !== '') {
            $where[] = '(s.title LIKE :q OR c.company_name LIKE :q)';
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }

        if ($currentUser['role'] === 'seller') {
            $where[] = 's.user_id = :user_id';
            $params['user_id'] = (int) $currentUser['id'];
        }

        $sql = 'SELECT s.*, c.company_name, u.name AS seller_name, q.title AS quote_title
                FROM sales s
                JOIN clients c ON c.id = s.client_id
                JOIN users u ON u.id = s.user_id
                LEFT JOIN quotes q ON q.id = s.quote_id';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY s.closed_at DESC, s.id DESC';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO sales
            (client_id, user_id, quote_id, title, amount, closed_at, notes, created_at)
            VALUES
            (:client_id, :user_id, :quote_id, :title, :amount, :closed_at, :notes, NOW())'
        );
        $stmt->execute([
            'client_id' => (int) $data['client_id'],
            'user_id' => (int) $data['user_id'],
            'quote_id' => $data['quote_id'] !== '' ? (int) $data['quote_id'] : null,
            'title' => $data['title'],
            'amount' => (float) $data['amount'],
            'closed_at' => $data['closed_at'],
            'notes' => $data['notes'] ?: null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function countBetween(array $currentUser, string $from, string $to): int
    {
        $sql = 'SELECT COUNT(*) FROM sales WHERE closed_at BETWEEN :from_date AND :to_date';
        $params = [
            'from_date' => $from,
            'to_date' => $to,
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
            "SELECT closed_at AS d, COUNT(*) AS total
             FROM sales
             WHERE user_id = :user_id
               AND closed_at BETWEEN :from_date AND :to_date
             GROUP BY closed_at"
        );
        $stmt->execute([
            'user_id' => $userId,
            'from_date' => $from,
            'to_date' => $to,
        ]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['d']] = (int) $row['total'];
        }
        return $map;
    }
}

