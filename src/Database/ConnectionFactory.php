<?php

declare(strict_types=1);

namespace App\Database;

use App\Config;
use PDO;
use PDOException;

final class ConnectionFactory
{
    private static ?PDO $pdo = null;

    public static function reset(): void
    {
        self::$pdo = null;
    }

    public static function get(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        Config::load();

        $driver = Config::getString('DB_DRIVER', 'mysql');

        try {
            if ($driver === 'sqlite') {
                $database = Config::getString('DB_DATABASE', ':memory:');
                $dsn = 'sqlite:' . $database;
                self::$pdo = new PDO($dsn);
            } else {
                $host = Config::getString('DB_HOST', '127.0.0.1');
                $port = Config::getString('DB_PORT', '3306');
                $name = Config::getString('DB_DATABASE', 'db_cartao');
                $user = Config::getString('DB_USERNAME', 'root');
                $pass = Config::getString('DB_PASSWORD', '');
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);
                self::$pdo = new PDO($dsn, $user, $pass);
            }

            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException('Falha na conexão com o banco de dados.', 0, $e);
        }

        return self::$pdo;
    }
}