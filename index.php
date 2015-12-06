<?php
header("Content-Type: application/json");
require 'vendor/autoload.php';
require_once 'models/User.php';
require_once 'models/Group.php';
require_once 'models/Reservation.php';

$app = new \Slim\Slim();
$app->get('/', function () {
    echo "RAUMSUCHE API";
});
$app->get('/users/:id', function ($id) {
	$user = new User(intval($id),"omgapassword","Test User", "I");
	
    echo json_encode($user);
});

$app->get('/reservations/:id', function ($id) {
$room = new Room(5,"A",0,array(666.666,777.777),45,array(new RoomProperty('Beamer')));
	$user = new User($id,"omgapassword","Test User", "I");
	$group = new Group("TestGroup", $user, array($user),"path/to/image");
	$reservation = new Reservation($room, $user, $group);
	
    echo json_encode($reservation);
});

$app->get('/groups/:id', function ($id) {
	$user = new User($id,"omgapassword","Test User", "I");
	$group = new Group("TestGroup", $user, array($user),"path/to/image");
	
    echo json_encode($group);
});
$app->run();