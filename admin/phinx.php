<?php

return [
    'paths' => [
        'migrations' => __DIR__ . '/action/db/migrations',
        'seeds' => __DIR__ . '/action/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'doorstep',
            'user' => 'root',
            'pass' => '123456',
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'production' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'doorstep',  // change if different
            'user' => 'root',
            'pass' => '123456',
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'doorstep_test',
            'user' => 'root',
            'pass' => '123456',
            'port' => '3306',
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation',
];
