<?php

namespace App\Core;

use PDO;

class Database
{
    public static function connect(): PDO
    {
        return new PDO(
            "mysql:host=localhost;dbname=doorstep;charset=utf8mb4",
            "root",
            "123456",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
}
