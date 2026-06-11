<?php
return [
    'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 3306,
    'database' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'test',
    'username' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '',
];