<?php

declare(strict_types=1);

namespace App\Models;

use Throwable;

final class Quote extends BaseModel
{
    public static function search(array $filters, array $currentUser): array
    {
        $where = [];
        $params = [];

        if (($filters['status'] ?? '') !== '') {
            $where[] = 'q.status = :status';
            $params['status'] = $filters['status'];
        }

        if (($filters['client_id'] ?? '') !== '') {
            $where[] = 'q.client_id = :client_id';
            $params['client_id'] = (int) $filters['client_id'];
        }

        if (($filters['q'] ?? '') !== '') {
            $where[] = '(q.title LIKE :q OR c.company_name LIKE :q)';
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }

        if ($currentUser['role'] === 'seller') {
            $where[] = 'q.user_id = :user_id';
            $params['user_id'] = (int) $currentUser['id'];
        }

        $sql = 'SELECT q.*, c.company_name, u.name AS seller_name
                FROM quotes q
                JOIN clients c ON c.id = q.client_id
                JOIN users u ON u.id = q.user_id';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY q.created_at DESC';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findAccessible(int $id, array $currentUser): ?array
    {
        $sql = 'SELECT q.*, c.company_name, u.name AS seller_name
                FROM quotes q
                JOIN clients c ON c.id = q.client_id
                JOIN users u ON u.id = q.user_id
                WHERE q.id = :id';
        $params = ['id' => $id];
        if ($currentUser['role'] === 'seller') {
            $sql .= ' AND q.user_id = :user_id';
            $params['user_id'] = (int) $currentUser['id'];
        }
        $sql .= ' LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $quote = $stmt->fetch();
        return $quote ?: null;
    }

    public static function createWithItems(array $data, array $items): int
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                'INSERT INTO quotes
                (client_id, user_id, source_activity_id, title, description, amount, status, created_at, sent_at, pdf_path)
                VALUES
                (:client_id, :user_id, :source_activity_id, :title, :description, :amount, :status, NOW(), :sent_at, :pdf_path)'
            );
            $stmt->execute([
                'client_id' => (int) $data['client_id'],
                'user_id' => (int) $data['user_id'],
                'source_activity_id' => $data['source_activity_id'] !== '' ? (int) $data['source_activity_id'] : null,
                'title' => $data['title'],
                'description' => $data['description'] ?: null,
                'amount' => $data['amount'],
                'status' => $data['status'],
                'sent_at' => $data['sent_at'] ?: null,
                'pdf_path' => $data['pdf_path'] ?: null,
            ]);
            $quoteId = (int) $db->lastInsertId();

            $itemStmt = $db->prepare(
                'INSERT INTO quote_items (quote_id, category_id, description, amount)
                 VALUES (:quote_id, :category_id, :description, :amount)'
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    'quote_id' => $quoteId,
                    'category_id' => (int) $item['category_id'],
                    'description' => $item['description'] ?: null,
                    'amount' => (float) $item['amount'],
                ]);
            }

            $db->commit();
            return $quoteId;
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    public static function items(int $quoteId): array
    {
        $stmt = self::db()->prepare(
            'SELECT qi.*, qc.name AS category_name
             FROM quote_items qi
             LEFT JOIN quote_categories qc ON qc.id = qi.category_id
             WHERE qi.quote_id = :quote_id
             ORDER BY qi.id ASC'
        );
        $stmt->execute(['quote_id' => $quoteId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $quoteId, string $status): void
    {
        $stmt = self::db()->prepare('UPDATE quotes SET status = :status WHERE id = :id');
        $stmt->execute([
            'id' => $quoteId,
            'status' => $status,
        ]);
    }

    public static function countBetween(array $currentUser, string $from, string $to): int
    {
        $sql = 'SELECT COUNT(*) FROM quotes WHERE created_at BETWEEN :from_date AND :to_date';
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
            "SELECT DATE(created_at) AS d, COUNT(*) AS total
             FROM quotes
             WHERE user_id = :user_id
               AND created_at BETWEEN :from_date AND :to_date
             GROUP BY DATE(created_at)"
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

