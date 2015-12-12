<?php

require_once 'Group.php';
require_once 'User.php';

class Reservation
{
    public $mRoom;
	public $mUser;
	public $mGroup;
	
	public function __construct(Room $pRoom, User $pUser, Group $pGroup){
		$this->mRoom = $pRoom;
		$this->mUser = $pUser;
		$this->mGroup = $pGroup;
	}


}