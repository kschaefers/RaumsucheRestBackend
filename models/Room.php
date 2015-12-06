<?php

require_once 'RoomProperty.php';

class Room
{
	public $mId;
    public $mBuilding;
	public $mFloor;
	public $mGeoPosition;
	public $mSize;
	public $mRoomProperties;
	
	public function __construct($pId, $pBuilding, $pFloor, Array $pGeoPosition, $pSize, Array $pRoomProperties){
		$this->mId = intval($pId);
		$this->mBuilding = $pBuilding;
		$this->mFloor = intval($pFloor);
		$this->mGeoPosition = $pGeoPosition;
		$this->mSize = intval($pSize);
		$this->mRoomProperties = $pRoomProperties;
	}
	
	
}