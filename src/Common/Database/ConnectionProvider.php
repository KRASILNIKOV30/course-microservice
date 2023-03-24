<?php

declare(strict_types=1);

namespace App\Common\Database;

final class ConnectionProvider
{
    public static function getConnection(): Connection
    {
        static $connection = null;
        if ($connection === null) {
            $dsn = 'mysql:dbname=course;host=127.0.0.1';
            $user = 'root';
            $password = 'Zakunbor7839';
            $connection = new Connection($dsn, $user, $password);
        }
        return $connection;
    }
}
