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
            Name = :name,
            Faculty = :faculty
            WHERE MtklNr = :mtklNr"
        );
        $st->execute(array(
            ':mtklNr' => $this->mtklNr,
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

    static function getAllGroupsOfUser($mtrklnr)
    {
        $pdo = db::getPDO();
        $st = $pdo->prepare(
            "SELECT g.Id AS GroupId, g.Name AS GroupName, g.OwnerId AS GroupOwnerId, g.GroupImage, u.MtklNr, u.Name AS UserName, u.Faculty
            FROM user_in_group AS uig
            LEFT JOIN groups AS g ON uig.groupId = g.Id
            LEFT JOIN users AS u ON uig.userId = u.MtklNr
            WHERE g.Id IN (SELECT groupId FROM user_in_group AS uig WHERE uig.userId = :mtklNr)
            ORDER BY g.Id"
        );
        $st->execute(array(
            ':mtklNr' => $mtrklnr
        ));
        $result = $st->fetchAll();
        $groups = array();
        for ($i = 0; $i < count($result); $i++) {
            if ($i > 0 && $result[$i - 1]['GroupId'] == $result[$i]['GroupId']) {
                $newMember = new User($result[$i]['MtklNr'], null, $result[$i]['UserName'], $result[$i]['Faculty']);
                $group = $groups[count($groups) - 1];
                $group->addMember($newMember);
            } else {
                $members = array();
                $members[] = new User($result[$i]['MtklNr'], null, $result[$i]['UserName'], $result[$i]['Faculty']);
                $groups[] = new Group($result[$i]['GroupId'], $result[$i]['GroupName'], $result[$i]['GroupOwnerId'], $members, $result[$i]['GroupImage']);
            }
        }
        return $groups;
    }

    static function getAllMeetingsOfUser($mtrklnr)
    {
        $pdo = db::getPDO();
        $st = $pdo->prepare(
            "SELECT m.* FROM meetings AS m
            WHERE UserGroup in
              (SELECT g.Id FROM user_in_group AS uig
              LEFT JOIN groups AS g ON uig.groupId = g.Id
              LEFT JOIN users AS u ON uig.userId = u.MtklNr
              WHERE g.Id IN (SELECT groupId FROM user_in_group AS uig WHERE uig.userId = :mtklNr))"
        );
        $st->execute(array(
            ':mtklNr' => $mtrklnr
        ));
        $result = $st->fetchAll();
        $meetings = array();
        for ($i = 0; $i < count($result); $i++) {
            $group = new Group($result[$i]['UserGroup'], null, null, array(''), null);
            $meetings[]=new Meeting($result[$i]['MeetingId'], $result[$i]['Room'], $group, $result[$i]['Day'], $result[$i]['Hour']);
        }
        return $meetings;
    }
}