<?php

declare(strict_types=1);

$type = getenv('DB_TYPE') ?: 'mysql';
$host = getenv('DB_HOST') ?: 'mysql';
$database = getenv('DB_DATABASE') ?: 'developmentdb';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'secret123';
