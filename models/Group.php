<?php

require_once 'User.php';

class Group
{
	public $name;
	public $owner;
	public $users;
	public $groupImage;

	public function __construct($pName, $pOwner, Array $pUsers, $pGroupImage = null){
		$this->name = $pName;
		$this->owner = $pOwner;
		$this->users = $pUsers;
		$this->groupImage = $pGroupImage;
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
        // add owner as group member
        $st1 = $pdo->prepare(
            "INSERT INTO user_in_group SET
            userId = :ownerId,
            groupId = :groupId"
        );
        $st1->execute(array(
            ':ownerId' => $this->owner,
            ':groupId' => $currentGroupId
        ));
        foreach ($this->users as $user) {
            // add others as group members
            $stUser = $pdo->prepare(
                "INSERT INTO user_in_group SET
            userId = :ownerId,
            groupId = :groupId"
            );
            $stUser->execute(array(
                ':ownerId' => $user,
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
            GroupImage = :groupImage"
        );
        $st->execute(array(
            ':name' => $this->name,
            ':ownerId' => $this->owner,
            ':groupImage' => $this->groupImage
        ));
        $currentGroupId = $pdo->lastInsertId();
        // add owner as group member
        $st1 = $pdo->prepare(
            "INSERT INTO user_in_group SET
            userId = :ownerId,
            groupId = :groupId"
        );
        $st1->execute(array(
            ':ownerId' => $this->owner,
            ':groupId' => $currentGroupId
        ));
        foreach ($this->users as $user) {
            // add others as group members
            $stUser = $pdo->prepare(
                "INSERT INTO user_in_group SET
            userId = :ownerId,
            groupId = :groupId"
            );
            $stUser->execute(array(
                ':ownerId' => $user,
                ':groupId' => $currentGroupId
            ));
        }
        $pdo->commit();
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
        $group = new Group($result['Name'], $result['OwnerId'], $result['Name'], $result['Faculty']);
        return $group;
    }
}