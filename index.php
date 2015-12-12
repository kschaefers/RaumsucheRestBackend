<?php

ini_set("log_errors", 1);
ini_set("error_log", "./php-error.log");
ini_set('error_reporting',E_ALL);
ini_set('display_errors',true);
error_log("Hello, errors!");

header("Content-Type: application/json");
require 'vendor/autoload.php';
require_once 'db.php';
require_once 'models/User.php';
require_once 'models/Group.php';
require_once 'models/Reservation.php';

function rand_passwd( $length = 8, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ) {
	return substr( str_shuffle( $chars ), 0, $length );
}

function sendEmail($studentId, $password){
	$header = 'From: Raumsuchenapp <no-reply@raumsuche.hsma.org>' . "\r\n" .
		'Reply-To: no-reply@raumsuche.hsma.org' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();

	mail($studentId.'@stud.hs-mannheim.de', 'Dein Passwort', "Dein Passwort für die Raumsuchenapp lautet:\n\n".$password, $header);
}

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

$app->put('/users/', function () use ($app) {
	$put = json_decode($app->request()->getBody());

	$password = rand_passwd();
	// make it a PHP associative array
	$putArray = get_object_vars($put);
	$user = new User($putArray['mtklNr'], md5($password), $putArray['name'], $putArray['faculty']);

	sendEmail($putArray['mtklNr'], $password);
	$user->add();

	echo json_encode($user);
});

$app->post('/users/:id', function ($id) use($app){
	$post = json_decode($app->request()->getBody());
	$postArray = get_object_vars($post);
	$user = new User($id,$postArray['password'],$postArray['name'],$postArray['faculty']);
	$user->update();
});

$app->delete('/users/:id',function($id){
	$deleted = User::deleteUserByMtrklNr($id);
	echo json_encode($deleted);
});

$app->get('/users/:id/groups', function ($id) {
	$groups = User::getAllGroupsOfUser($id);

	echo json_encode($groups);
});

// === RESERVATIONS ===
$app->get('/reservations/:id', function ($id) {
$room = "A212";
	$user = new User($id,"omgapassword","Test User", "I");
	$group = new Group("TestGroup", $user, array($user),"path/to/image");
	$reservation = new Reservation($room, $user, $group);

    echo json_encode($reservation);
});

// === ROOMS ===

$app->get('/rooms/', function () use($app) {
	$day = $app->request()->get('day');
	$hour = $app->request()->get('hour');
	$size = $app->request()->get('size');
	$computer = $app->request()->get('computer');
	$beamer = $app->request()->get('beamer');
	$pool = $app->request()->get('pool');
	$looseSeating = $app->request()->get('looseSeating');
	$video = $app->request()->get('video');
	$building = $app->request()->get('building');

	$roomArray = Room::Search($building,$day,$hour,$size,$computer,$beamer,$pool,$looseSeating,$video);

	echo json_encode($roomArray);
});

// === GROUPS ===
$app->get('/groups/:id', function ($id) {
	$user = new User($id,"omgapassword","Test User", "I");
	$group = new Group("TestGroup", $user, array($user),"path/to/image");

    echo json_encode($group);
});

// example json: {"name":"TestGroup","owner":1510651,"users":[123],"groupImage":"path\/to\/image"}
$app->put('/groups/',function() use($app){
	$put = json_decode($app->request()->getBody());

	// make it a PHP associative array
	$putArray = get_object_vars($put);
	$group = new Group($putArray['name'],$putArray['owner'],$putArray['users'],$putArray['groupImage']);
	$group->add();

	echo json_encode($group);
});

$app->post('/groups/:id', function ($id) use($app){
    $post = json_decode($app->request()->getBody());
    $postArray = get_object_vars($post);
    $group = new Group($postArray['name'],$postArray['owner'],$postArray['users'],$postArray['groupImage']);
    $group->update();
});

$app->get('/groups/:id', function ($id) {
	$group = Group::getGroupById($id);

	echo json_encode($group);
});

$app->run();