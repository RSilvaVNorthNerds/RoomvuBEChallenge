<?php

namespace App\Config;

use PDO;

class Database {
    public static function connect(): PDO {
        $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
