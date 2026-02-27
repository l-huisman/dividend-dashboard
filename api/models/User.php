<?php

declare(strict_types=1);

namespace Models;

class User
{
    public int $id = 0;
    public string $username = '';
    public string $email = '';
    public string $password_hash = '';
    public int $role = 0;
    public string $created_at = '';
    public string $updated_at = '';
}
