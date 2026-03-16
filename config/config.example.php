<?php

return [
    'app' => [
        'name' => 'Mini CRM',
        'env' => 'local',
        'debug' => true,
        'base_url' => '/',
        'timezone' => 'Europe/Rome',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'mini_crm',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'max_upload_bytes' => 5 * 1024 * 1024,
        'allowed_pdf_mime' => ['application/pdf'],
    ],
    'paths' => [
        'storage' => __DIR__ . '/../storage',
        'quote_uploads' => __DIR__ . '/../storage/uploads/quotes',
    ],
];

