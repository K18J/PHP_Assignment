<?php

use function Cake\Core\env;

return [
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    'Security' => [
        // Replace with a unique random string in production.
        'salt' => env('SECURITY_SALT', 'change-me-in-production-1234567890'),
    ],

    'Datasources' => [
        'default' => [
            'className' => \Cake\Database\Connection::class,
            'driver' => \Cake\Database\Driver\Mysql::class,
            'persistent' => false,
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'root',
            'database' => 'training_sessions',
            'encoding' => 'utf8mb4',
            'timezone' => 'UTC',
            'flags' => [],
            'cacheMetadata' => true,
            'log' => false,
        ],
        'test' => [
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'root',
            'database' => 'test_training_sessions',
            'url' => env('DATABASE_TEST_URL', 'sqlite://127.0.0.1/tmp/tests.sqlite'),
        ],
    ],
];

