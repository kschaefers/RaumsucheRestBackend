<?php

class User
{
    public $mtklNr;
    public $password;
    public $name;
    public $faculty;

    public function __construct($pMtklNr, $pPassword, $pName, $pFaculty)
    {
        $this->mtklNr = intval($pMtklNr);
        $this->password = $pPassword;
        $this->name = $pName;
        $this->faculty = $pFaculty;
    }


    function add()
    {
        $pdo = db::getPDO();
        $st = $pdo->prepare(
            "INSERT INTO users SET
            MtklNr = :mtklNr,
            Password = :password,
            Name = :name,
            Faculty = :faculty"
        );
        $st->execute(array(
            ':mtklNr' => $this->mtklNr,
            ':password' => $this->password,
            ':name' => $this->name,
            ':faculty' => $this->faculty
        ));
    }

    function update()
    {
        $pdo = db::getPDO();
        $st = $pdo->prepare(
            "UPDATE users SET
            Password = :password,
            Name = :name,
            Faculty = :faculty
            WHERE MtklNr = :mtklNr"
        );
        $st->execute(array(
            ':mtklNr' => $this->mtklNr,
            ':password' => $this->password,
            ':name' => $this->name,
            ':faculty' => $this->faculty
        ));
    }

    static function getUserByMtrklNr($mtrklnr)
    {
        $pdo = db::getPDO();
        $st = $pdo->prepare(
            "SELECT * FROM users WHERE
            MtklNr = :mtklNr"
        );
        $st->execute(array(
            ':mtklNr' => $mtrklnr
        ));
        $result = $st->fetch();
        $user = new User($result['MtklNr'], $result['Password'], $result['Name'], $result['Faculty']);
        return $user;
    }

    static function getUsers()
    {
        $pdo = db::getPDO();
        $st = $pdo->query("SELECT * FROM users");
        $result = $st->fetchAll();
        $users = array();
        foreach ($result as $userArray) {
            $users[] = new User($userArray['MtklNr'], $userArray['Password'], $userArray['Name'], $userArray['Faculty']);
        }

        return $users;
    }

    static function deleteUserByMtrklNr($mtrklnr)
    {
        $pdo = db::getPDO();
        $st = $pdo->prepare(
            "DELETE FROM users WHERE
            MtklNr = :mtklNr"
        );
        return $st->execute(array(
            ':mtklNr' => $mtrklnr
        ));
    }
}