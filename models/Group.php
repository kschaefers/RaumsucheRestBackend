<?php

require_once 'User.php';

class Group
{
	public $id;
    public $name;
	public $owner;
	public $users;
	public $groupImage;

	public function __construct($id = null, $pName, $pOwner, Array $pUsers, $pGroupImage = null){
		$this->id = $id;
        $this->name = $pName;
		$this->owner = $pOwner;
		$this->users = $pUsers;
		$this->groupImage = $pGroupImage;
	}

    function addMember($member) {
        $this->users[] = $member;
    }

    function removeMember($member){
        foreach($this->users as $key => $user){
            if($user->id == $member->id){
                unset($this->users[$key]);
                break;
            }
        }
        $this->users = array_values($this->users);
    }

	function add()
	{
		$pdo = db::getPDO();
		$pdo->beginTransaction();
        // create group
		$st = $pdo->prepare(
				"INSERT INTO groups SET
            Name = :name,
            OwnerId = :ownerId,
            GroupImage = :groupImage"
		);
		$st->execute(array(
				':name' => $this->name,
				':ownerId' => $this->owner,
				':groupImage' => $this->groupImage
		));
        $currentGroupId = $pdo->lastInsertId();
        $this->id = $currentGroupId;
        foreach ($this->users as $user) {
            // add others as group members
            $stUser = $pdo->prepare(
                "INSERT INTO user_in_group SET
            userId = :userId,
            groupId = :groupId"
            );
            $stUser->execute(array(
                ':userId' => $user->mtklNr,
                ':groupId' => $currentGroupId
            ));
        }
		$pdo->commit();
	}

    function update()
    {
        $pdo = db::getPDO();
        $pdo->beginTransaction();
        // create group
        $st = $pdo->prepare(
            "UPDATE groups SET
            Name = :name,
            OwnerId = :ownerId,
            GroupImage = :groupImage
            WHERE Id = :id"
        );
        $st->execute(array(
            ':name' => $this->name,
            ':ownerId' => $this->owner,
            ':groupImage' => $this->groupImage,
            ':id' => $this->id
        ));
        $stDeleteUsers = $pdo->prepare(
            "DELETE FROM user_in_group WHERE groupId = :id"
        );
        $stDeleteUsers->execute(array(
            ':id' => $this->id
        ));
        foreach ($this->users as $user) {
            // add others as group members
            $stUser = $pdo->prepare(
                "INSERT INTO user_in_group SET
            userId = :ownerId,
            groupId = :groupId"
            );
            $stUser->execute(array(
                ':ownerId' => $user->mtklNr,
                ':groupId' => $this->id
            ));
        }
        $pdo->commit();
    }

    static function getAllGroups()
    {
        $pdo = db::getPDO();
        $st = $pdo->query(
            "SELECT g.Id AS GroupId, g.Name AS GroupName, g.OwnerId AS GroupOwnerId, g.GroupImage, u.MtklNr, u.Name AS UserName, u.Faculty
            FROM user_in_group AS uig
            LEFT JOIN groups AS g ON uig.groupId = g.Id
            LEFT JOIN users AS u ON uig.userId = u.MtklNr
            ORDER BY g.Id"
        );
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

    static function getGroupById($id) {
        $pdo = db::getPDO();
        $st = $pdo->prepare(
            "SELECT * FROM groups WHERE
            Id = :id"
        );
        $st->execute(array(
            ':id' => $id
        ));
        $result = $st->fetch();
        $members = array();
        $stMembers = $pdo->prepare(
            "SELECT * FROM users WHERE MtklNr IN (SELECT userId FROM user_in_group WHERE groupId = :id)"
        );
        $stMembers->execute(array(
            ':id' => $id
        ));
        $resultMembers = $stMembers->fetchAll();
        foreach ($resultMembers as $member) {
            $members[] = new User($member['MtklNr'], null, $member['Name'], $member['Faculty']);
        }

        $group = new Group($id, $result['Name'], $result['OwnerId'], $members, $result['GroupImage']);
        return $group;
    }

    static function deleteGroupById($id)
    {
        $pdo = db::getPDO();
        $st = $pdo->prepare(
            "DELETE FROM groups WHERE Id = :id"
        );
        $result =  $st->execute(array(
            ':id' => $id
        ));
        return $result;
    }


    static function getAllMeetingsOfGroup($groupId)
    {
        $pdo = db::getPDO();
        $st = $pdo->prepare(
            "SELECT * FROM meetings
            WHERE UserGroup = :groupId
            ORDER BY g.Id"
        );
        $st->execute(array(
            ':groupId' => $groupId
        ));
        $result = $st->fetchAll();
        $meetings = array();
        foreach($result as $meetingObject){
            $meeting = new Meeting($meetingObject->MeetingId,$meetingObject->Room,$meetingObject->UserGroup,$meetingObject->Day,$meetingObject->Hour);
            $meetings[] = $meeting;
        }
        return $meetings;
    }
}