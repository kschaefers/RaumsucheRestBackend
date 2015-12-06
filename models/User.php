<?php

class User
{
    public $mMtklNr;
	public $mPassword;
	public $mName;
	public $mFaculty;
	
	public function __construct($pMtklNr, $pPassword, $pName, $pFaculty){
		$this->mMtklNr = intval($pMtklNr);
		$this->mPassword = $pPassword;
		$this->mName = $pName;
		$this->mFaculty = $pFaculty;
	}
	
	
}