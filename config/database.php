<?php
return [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_NAME'] ?? 'test',      // Changed from 'name'
    'username' => $_ENV['DB_USER'] ?? 'root',      // Changed from 'user'
    'password' => $_ENV['DB_PASS'] ?? '',          // Changed from 'pass'
];