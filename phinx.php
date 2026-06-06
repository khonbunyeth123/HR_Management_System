<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$dotenvFile = __DIR__ . '/.env';
if (is_file($dotenvFile)) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
}

$env = static function (string $key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);
    return $value === false || $value === null || $value === '' ? $default : $value;
};

return [
    'paths' => [
        'migrations' => __DIR__ . '/database/migrations',
        'seeds' => __DIR__ . '/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => $env('DB_HOST', 'localhost'),
            'name' => $env('DB_NAME', 'doorstep'),
            'user' => $env('DB_USER', 'root'),
            'pass' => $env('DB_PASS', ''),
            'port' => $env('DB_PORT', '3306'),
            'charset' => $env('DB_CHARSET', 'utf8mb4'),
        ],
        'production' => [
            'adapter' => 'mysql',
            'host' => $env('DB_HOST', 'localhost'),
            'name' => $env('DB_NAME', 'doorstep'),
            'user' => $env('DB_USER', 'root'),
            'pass' => $env('DB_PASS', ''),
            'port' => $env('DB_PORT', '3306'),
            'charset' => $env('DB_CHARSET', 'utf8mb4'),
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => $env('DB_HOST', 'localhost'),
            'name' => $env('DB_NAME', 'doorstep_test'),
            'user' => $env('DB_USER', 'root'),
            'pass' => $env('DB_PASS', ''),
            'port' => $env('DB_PORT', '3306'),
            'charset' => $env('DB_CHARSET', 'utf8mb4'),
        ],
    ],
    'version_order' => 'creation',
];
