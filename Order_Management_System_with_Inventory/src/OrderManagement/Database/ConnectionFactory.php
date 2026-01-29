<?php

declare(strict_types=1);

namespace OrderManagement\Database;

use PDO;
use RuntimeException;

final class ConnectionFactory
{
    private static ?PDO $pdo = null;

    public static function make(string $configPath): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        if (!file_exists($configPath)) {
            throw new RuntimeException("Database config not found at {$configPath}");
        }

        $config = require $configPath;

        self::$pdo = new PDO(
            $config['dsn'],
            $config['user'],
            $config['password'],
            $config['options']
        );

        return self::$pdo;
    }
}