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
        require __DIR__ . '/../dbconfig.php';

        try {
            $this->connection = new PDO(
                "{$type}:host={$host};dbname={$database}",
                $username,
                $password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage());
        }
    }
}
