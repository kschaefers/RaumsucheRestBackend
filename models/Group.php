<?php

require_once 'User.php';

class Group
{
	public $mName;
	public $mOwner;
	public $mUsers;
	public $mGroupImage;
	
	public function __construct($pName, User $pOwner, Array $pUsers, $pGroupImage = null){
		$this->mName = $pName;
		$this->mOwner = $pOwner;
		$this->mUsers = $pUsers;
		$this->mGroupImage = $pGroupImage;
	}
	
	
}