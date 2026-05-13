<?php

declare(strict_types=1);

namespace App\Models;

final class Client extends BaseModel
{
    private const OPTIONAL_CONTACT_COLUMNS = [
        'contact_role' => 'contact_role',
        'secondary_contact_name' => 'secondary_contact_name',
        'secondary_contact_role' => 'secondary_contact_role',
        'second_email' => 'secondary_email',
        'second_phone' => 'secondary_phone',
    ];

    public static function search(array $filters, array $currentUser): array
    {
        $where = [];
        $params = [];

        if (($filters['q'] ?? '') !== '') {
            $searchColumns = ['c.company_name', 'c.contact_name', 'c.email', 'c.phone'];
            foreach (['secondary_contact_name', 'second_email', 'second_phone'] as $column) {
                if (self::hasColumn($column)) {
                    $searchColumns[] = 'c.' . $column;
                }
            }

            $where[] = '(' . implode(' OR ', array_map(
                static fn(string $column): string => $column . ' LIKE :q',
                $searchColumns
            )) . ')';
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
        return array_map([self::class, 'withClientDefaults'], $stmt->fetchAll());
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
        return $client ? self::withClientDefaults($client) : null;
    }

    public static function create(array $data): int
    {
        $columns = self::writableColumns();
        $columnNames = array_keys($columns);
        $placeholders = array_map(static fn(string $column): string => ':' . $column, $columnNames);

        $stmt = self::db()->prepare(
            'INSERT INTO clients (' . implode(', ', $columnNames) . ', created_at)
             VALUES (' . implode(', ', $placeholders) . ', NOW())'
        );
        $stmt->execute(self::paramsForColumns($data, $columns));
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $columns = self::writableColumns();
        $assignments = array_map(
            static fn(string $column): string => $column . ' = :' . $column,
            array_keys($columns)
        );
        $params = self::paramsForColumns($data, $columns);
        $params['id'] = $id;

        $stmt = self::db()->prepare(
            'UPDATE clients SET ' . implode(', ', $assignments) . ' WHERE id = :id'
        );
        $stmt->execute($params);
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

    /**
     * @return array<string, string>
     */
    private static function writableColumns(): array
    {
        $columns = [
            'company_name' => 'company_name',
            'contact_name' => 'contact_name',
        ];

        foreach (self::OPTIONAL_CONTACT_COLUMNS as $column => $dataKey) {
            if (self::hasColumn($column)) {
                $columns[$column] = $dataKey;
            }
        }

        return $columns + [
            'email' => 'email',
            'phone' => 'phone',
            'address' => 'address',
            'website' => 'website',
            'notes' => 'notes',
            'owner_user_id' => 'owner_user_id',
            'is_shared' => 'is_shared',
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $columns
     * @return array<string, mixed>
     */
    private static function paramsForColumns(array $data, array $columns): array
    {
        $params = [];
        foreach ($columns as $column => $dataKey) {
            if ($column === 'owner_user_id') {
                $params[$column] = (int) $data[$dataKey];
                continue;
            }

            if ($column === 'is_shared') {
                $params[$column] = !empty($data[$dataKey]) ? 1 : 0;
                continue;
            }

            $value = (string) ($data[$dataKey] ?? '');
            $params[$column] = in_array($column, ['company_name', 'contact_name'], true)
                ? $value
                : ($value !== '' ? $value : null);
        }

        return $params;
    }

    private static function hasColumn(string $column): bool
    {
        $columns = self::tableColumns();
        return isset($columns[$column]);
    }

    /**
     * @return array<string, true>
     */
    private static function tableColumns(): array
    {
        static $columns = null;
        if ($columns !== null) {
            return $columns;
        }

        $columns = [];
        $stmt = self::db()->query('SHOW COLUMNS FROM clients');
        foreach ($stmt->fetchAll() as $column) {
            $columns[(string) $column['Field']] = true;
        }

        return $columns;
    }

    /**
     * @param array<string, mixed> $client
     * @return array<string, mixed>
     */
    private static function withClientDefaults(array $client): array
    {
        foreach (array_keys(self::OPTIONAL_CONTACT_COLUMNS) as $column) {
            if (!array_key_exists($column, $client)) {
                $client[$column] = null;
            }
        }

        return $client;
    }

    public static function timeline(int $clientId, array $currentUser): array
    {
        $params = [
            'activity_client_id' => $clientId,
            'appointment_client_id' => $clientId,
            'quote_client_id' => $clientId,
            'sale_client_id' => $clientId,
        ];

        $activityFilter = '';
        $appointmentFilter = '';
        $quoteFilter = '';
        $saleFilter = '';
        if ($currentUser['role'] === 'seller') {
            $params['activity_user_id'] = (int) $currentUser['id'];
            $params['appointment_user_id'] = (int) $currentUser['id'];
            $params['quote_user_id'] = (int) $currentUser['id'];
            $params['sale_user_id'] = (int) $currentUser['id'];
            $activityFilter = ' AND a.user_id = :activity_user_id';
            $appointmentFilter = ' AND ap.user_id = :appointment_user_id';
            $quoteFilter = ' AND q.user_id = :quote_user_id';
            $saleFilter = ' AND s.user_id = :sale_user_id';
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
                WHERE a.client_id = :activity_client_id {$activityFilter}
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
                WHERE ap.client_id = :appointment_client_id {$appointmentFilter}
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
                WHERE q.client_id = :quote_client_id {$quoteFilter}
                UNION ALL
                SELECT
                    s.id,
                    CONCAT(s.closed_at, ' 00:00:00') AS event_at,
                    'sale' AS event_type,
                    CONCAT('Vendita: ', s.title, ' - EUR ', FORMAT(s.amount, 2)) AS title,
                    s.notes AS description,
                    u.name AS actor
                FROM sales s
                JOIN users u ON u.id = s.user_id
                WHERE s.client_id = :sale_client_id {$saleFilter}
            ) t
            ORDER BY event_at DESC, id DESC
        ";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
