<?php

declare(strict_types=1);

namespace App\Models;

final class Client extends BaseModel
{
    public static function search(array $filters, array $currentUser): array
    {
        $where = [];
        $params = [];

        if (($filters['q'] ?? '') !== '') {
            $where[] = '(c.company_name LIKE :q OR c.contact_name LIKE :q OR c.email LIKE :q OR c.phone LIKE :q)';
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }

        if (($filters['owner_user_id'] ?? '') !== '') {
            $where[] = 'c.owner_user_id = :owner_user_id';
            $params['owner_user_id'] = (int) $filters['owner_user_id'];
        }

        if ($currentUser['role'] === 'seller') {
            $where[] = '(c.owner_user_id = :current_user_id OR c.is_shared = 1)';
            $params['current_user_id'] = (int) $currentUser['id'];
        }

        $sql = 'SELECT c.*, u.name AS owner_name
                FROM clients c
                JOIN users u ON u.id = c.owner_user_id';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY c.created_at DESC';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findAccessible(int $id, array $currentUser): ?array
    {
        $sql = 'SELECT c.*, u.name AS owner_name
                FROM clients c
                JOIN users u ON u.id = c.owner_user_id
                WHERE c.id = :id';
        $params = ['id' => $id];

        if ($currentUser['role'] === 'seller') {
            $sql .= ' AND (c.owner_user_id = :current_user_id OR c.is_shared = 1)';
            $params['current_user_id'] = (int) $currentUser['id'];
        }

        $sql .= ' LIMIT 1';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $client = $stmt->fetch();
        return $client ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO clients
            (company_name, contact_name, email, phone, address, website, notes, owner_user_id, is_shared, created_at)
            VALUES
            (:company_name, :contact_name, :email, :phone, :address, :website, :notes, :owner_user_id, :is_shared, NOW())'
        );
        $stmt->execute([
            'company_name' => $data['company_name'],
            'contact_name' => $data['contact_name'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'] ?: null,
            'address' => $data['address'] ?: null,
            'website' => $data['website'] ?: null,
            'notes' => $data['notes'] ?: null,
            'owner_user_id' => (int) $data['owner_user_id'],
            'is_shared' => !empty($data['is_shared']) ? 1 : 0,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE clients
             SET company_name = :company_name,
                 contact_name = :contact_name,
                 email = :email,
                 phone = :phone,
                 address = :address,
                 website = :website,
                 notes = :notes,
                 owner_user_id = :owner_user_id,
                 is_shared = :is_shared
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'company_name' => $data['company_name'],
            'contact_name' => $data['contact_name'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'] ?: null,
            'address' => $data['address'] ?: null,
            'website' => $data['website'] ?: null,
            'notes' => $data['notes'] ?: null,
            'owner_user_id' => (int) $data['owner_user_id'],
            'is_shared' => !empty($data['is_shared']) ? 1 : 0,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = self::db()->prepare('DELETE FROM clients WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function forSelect(array $currentUser): array
    {
        $sql = 'SELECT id, company_name FROM clients';
        $params = [];
        if ($currentUser['role'] === 'seller') {
            $sql .= ' WHERE owner_user_id = :uid OR is_shared = 1';
            $params['uid'] = (int) $currentUser['id'];
        }
        $sql .= ' ORDER BY company_name ASC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function timeline(int $clientId, array $currentUser): array
    {
        $params = ['client_id' => $clientId];

        $activityFilter = '';
        $appointmentFilter = '';
        $quoteFilter = '';
        $saleFilter = '';
        if ($currentUser['role'] === 'seller') {
            $params['user_id'] = (int) $currentUser['id'];
            $activityFilter = ' AND a.user_id = :user_id';
            $appointmentFilter = ' AND ap.user_id = :user_id';
            $quoteFilter = ' AND q.user_id = :user_id';
            $saleFilter = ' AND s.user_id = :user_id';
        }

        $sql = "
            SELECT * FROM (
                SELECT
                    a.id,
                    a.occurred_at AS event_at,
                    'activity' AS event_type,
                    CONCAT(UPPER(a.type), ': ', a.subject) AS title,
                    a.body AS description,
                    u.name AS actor
                FROM activities a
                JOIN users u ON u.id = a.user_id
                WHERE a.client_id = :client_id {$activityFilter}
                UNION ALL
                SELECT
                    ap.id,
                    ap.start_at AS event_at,
                    'appointment' AS event_type,
                    CONCAT('Appuntamento: ', ap.title) AS title,
                    ap.description AS description,
                    u.name AS actor
                FROM appointments ap
                JOIN users u ON u.id = ap.user_id
                WHERE ap.client_id = :client_id {$appointmentFilter}
                UNION ALL
                SELECT
                    q.id,
                    q.created_at AS event_at,
                    'quote' AS event_type,
                    CONCAT('Preventivo: ', q.title, ' (', q.status, ')') AS title,
                    q.description AS description,
                    u.name AS actor
                FROM quotes q
                JOIN users u ON u.id = q.user_id
                WHERE q.client_id = :client_id {$quoteFilter}
                UNION ALL
                SELECT
                    s.id,
                    CONCAT(s.closed_at, ' 00:00:00') AS event_at,
                    'sale' AS event_type,
                    CONCAT('Vendita: ', s.title, ' - €', FORMAT(s.amount, 2)) AS title,
                    s.notes AS description,
                    u.name AS actor
                FROM sales s
                JOIN users u ON u.id = s.user_id
                WHERE s.client_id = :client_id {$saleFilter}
            ) t
            ORDER BY event_at DESC
        ";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
