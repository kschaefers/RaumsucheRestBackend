<?php

/**Sorry!!! Hier die DB Zugangsdaten:
 * User: d02043d3
 * DB-Name: d02043d3
 * Passwort: h8rQLgrhrRNZoZGw
 */

class db
{



    static function getPDO() {
        $dsn = 'mysql:dbname=d02043d3;host=127.0.0.1';
        $user = 'd02043d3';
        $password = 'h8rQLgrhrRNZoZGw';
        static $db = null;
        if (null === $db)
            $db = new PDO($dsn,$user,$password);
        return $db;
    }
}