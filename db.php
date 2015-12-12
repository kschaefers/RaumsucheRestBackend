<?php

class db
{



    static function getPDO() {
        $dsn = 'mysql:dbname=d02043d3;host=127.0.0.1';
        $user = 'd02043d3';
        $password = 'vD6TAg5fXgduY2fM';
        static $db = null;
        if (null === $db)
            $db = new PDO($dsn,$user,$password);
        return $db;
    }
}
