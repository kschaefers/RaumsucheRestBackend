<?php

require_once 'Group.php';
require_once 'User.php';

class Meeting
{
	public $meetingId;
    public $room;
	public $group;
	public $day;
	public $hour;
	
	public function __construct($meetingId, $pRoom, Group $pGroup, $pDay, $pHour){
		$this->meetingId = $meetingId;
		$this->room = $pRoom;
		$this->group = $pGroup;
		$this->day = $pDay;
		$this->hour = $pHour;
	}

	function add()
	{
		$pdo = db::getPDO();
		// create group
		$st = $pdo->prepare(
			"INSERT INTO meetings SET
            Room = :room,
            UserGroup = :groupid,
            Day = :day,
            Hour = :hour"
		);
		$st->execute(array(
			':room' => $this->room,
			':groupid' => $this->group->id,
			':day' => $this->day,
			':hour' => $this->hour
		));
		$this->meetingId = $pdo->lastInsertId();
	}

	function update()
	{
		$pdo = db::getPDO();
		$st = $pdo->prepare(
			"UPDATE meetings SET
            Room = :room,
            UserGroup = :groupid,
            Day = :day,
            Hour = :hour
            WHERE MeetingId = :meetingId"
		);
		$st->execute(array(
			':meetingId' => $this->meetingId,
			':room' => $this->room,
			':groupid' => $this->group->id,
			':day' => $this->day,
			':hour' => $this->hour
		));
	}

	static function getMeetings()
	{
		$pdo = db::getPDO();
		$st = $pdo->query("SELECT * FROM meetings");
		$result = $st->fetchAll();
		$meetings = array();
		foreach ($result as $meetingArray) {
			$group = Group::getGroupById($meetingArray['UserGroup']);
			$meetings[] = new Meeting($meetingArray['MeetingId'], $meetingArray['Room'], $group, $meetingArray['Day'], $meetingArray['Hour']);
		}

		return $meetings;
	}


	static function getMeetingById($id)
	{
		$pdo = db::getPDO();
		$st = $pdo->prepare(
			"SELECT * FROM meetings WHERE
            MeetingId = :meetingId"
		);
		$st->execute(array(
			':meetingId' => $id
		));
		$result = $st->fetch();
		$group = Group::getGroupById($result['UserGroup']);
		$meeting = new Meeting($result['MeetingId'], $result['Room'], $group, $result['Day'], $result['Hour']);
		return $meeting;
	}

	static function deleteMeetingById($id)
	{
		$pdo = db::getPDO();
		$st = $pdo->prepare(
			"DELETE FROM meetings WHERE MeetingId = :meetingId"
		);
		$result =  $st->execute(array(
			':meetingId' => $id
		));
		return $result;
	}
}