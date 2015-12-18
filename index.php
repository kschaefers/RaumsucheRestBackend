<?php

ini_set("log_errors", 1);
ini_set("error_log", "./php-error.log");
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

header("Content-Type: application/json");
require 'vendor/autoload.php';
require_once 'db.php';
require_once 'models/User.php';
require_once 'models/Group.php';
require_once 'models/Room.php';
require_once 'models/Meeting.php';

use \Slim\Middleware\HttpBasicAuthentication\PdoAuthenticator;

function rand_passwd($length = 8, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
{
    return substr(str_shuffle($chars), 0, $length);
}

function sendEmail($studentId, $password)
{
    $header = 'From: Raumsuchenapp <no-reply@raumsuche.hsma.org>' . "\r\n" .
        'Reply-To: no-reply@raumsuche.hsma.org' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    mail($studentId . '@stud.hs-mannheim.de', 'Dein Passwort', "Dein Passwort fuer die Raumsuchenapp lautet:\n\n" . $password, $header);
}

$container = new \Slim\Container(['settings' => ['displayErrorDetails' => true]]);
$app = new \Slim\App($container);

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => ["/users", "/groups"],
    "realm" => "Protected",
    "secure" => false,
    "environment" => "REDIRECT_HTTP_AUTHORIZATION",
    "authenticator" => new PdoAuthenticator([
        "pdo" => db::getPDO(),
        "table" => "users",
        "user" => "MtklNr",
        "hash" => "Password"
    ])
]));

$app->get('/', function () {
    echo "RAUMSUCHE API";
});

// === USERS ===
$app->get('/users', function ($request, $response, $args) {

    $users = User::getUsers();

    echo json_encode($users);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $user = User::getUserByMtrklNr($args['id']);

    echo json_encode($user);
});

$app->put('/register', function ($request, $response, $args) {
    $put = json_decode($request->getBody());

    $password = rand_passwd();
    // make it a PHP associative array
    $putArray = get_object_vars($put);
    $user = new User($putArray['mtklNr'], password_hash($password, PASSWORD_DEFAULT), $putArray['name'], $putArray['faculty']);

    sendEmail($putArray['mtklNr'], $password);
    $user->add();

    echo json_encode($user);
});

$app->post('/users/{id}', function ($request, $response, $args) {
    $server_params = $request->getServerParams();
    if (preg_match("/Basic\s+(.*)$/i", $server_params[$this->options["environment"]], $matches)) {
        list($user, $password) = explode(":", base64_decode($matches[1]));
    }
    if ($args['id'] == $user) {
        $post = json_decode($request->getBody());
        $postArray = get_object_vars($post);
        $user = new User($args['id'], $postArray['password'], $postArray['name'], $postArray['faculty']);
        $user->update();
        echo json_encode($user);
    } else {
        echo json_encode(false);
    }
});

$app->delete('/users/{id}', function ($request, $response, $args) {
    $server_params = $request->getServerParams();
    if (preg_match("/Basic\s+(.*)$/i", $server_params[$this->options["environment"]], $matches)) {
        list($user, $password) = explode(":", base64_decode($matches[1]));
    }
    if ($args['id'] == $user) {
        $user = User::getUserByMtrklNr($args['id']);
        $groups = User::getAllGroupsOfUser($args['id']);
        foreach($groups as $group){
            $group->removeMember($user);
            if(empty($group->users)){
                Group::deleteGroupById($group->id);
            }
            if($group->owner == $args['id']){
                $group->owner = $group->users[0];
            }
        }
        $deleted = User::deleteUserByMtrklNr($args['id']);
        echo json_encode($deleted);
    } else {
        echo json_encode(false);
    }

});

$app->get('/users/{id}/groups', function ($request, $response, $args) {
    $groups = User::getAllGroupsOfUser($args['id']);

    echo json_encode($groups);
});

// === MEETINGS ===
$app->get('/meetings/{id}', function ($request, $response, $args) {
    $meeting = Meeting::getMeetingById($args['id']);

    echo json_encode($meeting);
});

$app->put('/meetings', function ($request, $response, $args) {
    $put = json_decode($request->getBody());

    // make it a PHP associative array
    $putArray = get_object_vars($put);
    $meeting = new Meeting(null, $putArray['room'], $putArray['groupId'], $putArray['day'], $putArray['hour']);
    $meeting->add();

    echo json_encode($meeting);
});

$app->post('/meetings/{id}', function ($request, $response, $args) {

        $post = json_decode($request->getBody());
        $postArray = get_object_vars($post);
        $meeting = new Meeting($args['id'], $postArray['room'], $postArray['groupId'], $postArray['day'], $postArray['hour']);
        $meeting->update();
        echo json_encode($meeting);
});

$app->delete('/meetings/{id}', function ($request, $response, $args) {

        $deleted = Meetings::deleteMeetingById($args['id']);
        echo json_encode($deleted);

});

// === ROOMS ===

$app->get('/rooms', function ($request, $response, $args) {

    $building = $day = $hour = $size = $computer = $beamer = $pool = $looseSeating = $video = null;
    if (array_key_exists('day', $request->getQueryParams())) {
        $day = $request->getQueryParams()['day'];
    }
    if (array_key_exists('hour', $request->getQueryParams())) {
        $hour = $request->getQueryParams()['hour'];
    }
    if (array_key_exists('size', $request->getQueryParams())) {
        $size = $request->getQueryParams()['size'];
    }
    if (array_key_exists('computer', $request->getQueryParams())) {
        $computer = $request->getQueryParams()['computer'];
    }
    if (array_key_exists('beamer', $request->getQueryParams())) {
        $beamer = $request->getQueryParams()['beamer'];
    }
    if (array_key_exists('pool', $request->getQueryParams())) {
        $pool = $request->getQueryParams()['pool'];
    }
    if (array_key_exists('looseSeating', $request->getQueryParams())) {
        $looseSeating = $request->getQueryParams()['looseSeating'];
    }
    if (array_key_exists('video', $request->getQueryParams())) {
        $video = $request->getQueryParams()['video'];
    }
    if (array_key_exists('building', $request->getQueryParams())) {
        $building = $request->getQueryParams()['building'];
    }

    $roomArray = Room::Search($building, $day, $hour, $size, $computer, $beamer, $pool, $looseSeating, $video);

    echo json_encode($roomArray);
});

// === GROUPS ===
$app->put('/groups', function ($request, $response, $args) {
    $put = json_decode($request->getBody());

    // make it a PHP associative array
    $putArray = get_object_vars($put);
    $members = array();
    $tmpMembers = $putArray['users'];
    foreach ($tmpMembers as $tmpmember) {
        $member = get_object_vars($tmpmember);
        $members[] = new User($member['mtklNr'], '', '', '');
    }
    $group = new Group(null, $putArray['name'], $putArray['owner'], $members, $putArray['groupImage']);
    $group->add();

    echo json_encode($group);
});

$app->get('/groups', function ($request, $response, $args) {
    $groups = Group::getAllGroups();

    echo json_encode($groups);
});

$app->get('/groups/{id}', function ($request, $response, $args) {
    $group = Group::getGroupById($args['id']);

    echo json_encode($group);
});

$app->get('/groups/{id}/meetings', function ($request, $response, $args) {
    $meetings = Group::getAllMeetingsOfGroup($args['id']);

    echo json_encode($meetings);
});

$app->post('/groups/{id}', function ($request, $response, $args) {
    $post = json_decode($request->getBody());
    $postArray = get_object_vars($post);
    $members = array();
    $tmpMembers = $postArray['users'];
    foreach ($tmpMembers as $tmpmember) {
        $member = get_object_vars($tmpmember);
        $members[] = new User($member['mtklNr'], '', '', '');
    }
    $group = new Group($args['id'], $postArray['name'], $postArray['owner'], $members, $postArray['groupImage']);
    $group->update();
    echo json_encode($group);
});

$app->delete('/groups/{id}', function ($request, $response, $args) {
    $group = Group::getGroupById($args['id']);
    $server_params = $request->getServerParams();
    if (preg_match("/Basic\s+(.*)$/i", $server_params[$this->options["environment"]], $matches)) {
        list($user, $password) = explode(":", base64_decode($matches[1]));
    }
    if ($group->owner == $user) {
        $deleted = Group::deleteGroupById($args['id']);
        echo json_encode($deleted);
    } else {
        echo json_encode(false);
    }
});


$app->run();