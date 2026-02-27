<?php

declare(strict_types=1);

namespace Repositories;

use PDO;
use PDOException;

class Repository
{
    protected PDO $connection;

    public function __construct()
    {
        /** @var array{type: string, host: string, database: string, username: string, password: string} $config */
        $config = (static function (): array {
            require __DIR__ . '/../dbconfig.php';

            /** @var string $type */
            /** @var string $host */
            /** @var string $database */
            /** @var string $username */
            /** @var string $password */
            return [
                'type' => $type,
                'host' => $host,
                'database' => $database,
                'username' => $username,
                'password' => $password,
            ];
        })();

        try {
            $this->connection = new PDO(
                "{$config['type']}:host={$config['host']};dbname={$config['database']}",
                $config['username'],
                $config['password']
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage());
        }
    }
}
