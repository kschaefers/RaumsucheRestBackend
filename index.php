<?php
header("Content-Type: application/json");
require 'vendor/autoload.php';
require_once 'db.php';
require_once 'models/User.php';
require_once 'models/Group.php';
require_once 'models/Reservation.php';

$app = new \Slim\Slim();
$app->get('/', function () {
    echo "RAUMSUCHE API";
});

// === USERS ===
$app->get('/users/', function(){

	$users = User::getUsers();

	echo json_encode($users);
});

$app->get('/users/:id', function ($id) {
	$user = User::getUserByMtrklNr($id);
	
    echo json_encode($user);
});

$app->put('/users/',function() use($app){
	$put = json_decode($app->request()->getBody());

	// make it a PHP associative array
	$putArray = get_object_vars($put);
	$user = new User($putArray['mtklNr'],$putArray['password'],$putArray['name'],$putArray['faculty']);
	$user->add();

	echo json_encode($user);
});

$app->post('/users/:id', function ($id) use($app){
	$post = json_decode($app->request()->getBody());
	$postArray = get_object_vars($post);
	var_dump($postArray);
	$user = new User($id,$postArray['password'],$postArray['name'],$postArray['faculty']);
	$user->update();
});

$app->delete('/users/:id',function($id){
	$deleted = User::deleteUserByMtrklNr($id);
	echo json_encode($deleted);
});

// === RESERVATIONS ===
$app->get('/reservations/:id', function ($id) {
$room = new Room(5,"A",0,array(666.666,777.777),45,array(new RoomProperty('Beamer')));
	$user = new User($id,"omgapassword","Test User", "I");
	$group = new Group("TestGroup", $user, array($user),"path/to/image");
	$reservation = new Reservation($room, $user, $group);
	
    echo json_encode($reservation);
});

// === GROUPS ===
$app->get('/groups/:id', function ($id) {
	$user = new User($id,"omgapassword","Test User", "I");
	$group = new Group("TestGroup", $user, array($user),"path/to/image");
	
    echo json_encode($group);
});
$app->run();