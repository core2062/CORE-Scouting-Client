<?php
/*
TODO: add openid based sign-up (perhaps a accordian to hold different sign-up methods)

This script handles: signup (because process.php requires token)
*/

//set place & type (for logging)
$place = 'signup.php';
$type = 'signup';

require 'php/general.php';

//get input variables
//variables_order must contain "C" in php.ini
$input = $_COOKIE['user'] or logout("user object was not received");
$input = json_decode($input, true);


//check for missing required data (for optional, assign default)

//build list of required inputs
$objects = array('info', 'account');
$required = array();//initialize empty

$len = sizeof($objects);
for($i = 0; $i < $len; $i++){
	$e = array_keys($userObject[$objects[$i]]);
	$len2 = sizeof($e);
	for($f = 0; $f < $len2; $f++){
		$e[$f] = array($objects[$i], $e[$f]);
	}
	$required = array_merge($required, $e);
}

//check everything that's required
$len = sizeof($required);
for($i = 0; $i < $len; $i++){
	if (empty($input[ $required[$i][0] ][ $required[$i][1] ]) == true){
		send_error($required[$i][1] . ' wasn\'t not sent');
	}
}

//transfer all stuff into a new object to prevent storing any extra values & do default check
$input = array(
	'permission' => 1, // TODO: make system to assign higher permissions automatically

	"info"=> array(
		"fName"=> $input['info']['fName'],
		"lName"=> $input['info']['lName'],
		"team"=> (int)$input['info']['team']
	),

	"prefs"=> array(
		"fade"=> checkDefault($input['prefs']['fade'], true),
		"verbose"=> checkDefault($input['prefs']['verbose'], true)
	),

	"account"=> array(
		"pword"=> $input['account']['pword'],
		"email"=> $input['account']['email']
	),
	
	"opt"=> array(
		"zip"=> checkDefault($input['opt']['zip'], ''),
		"browser"=> checkDefault($input['opt']['browser'], ''),
		"gender"=> checkDefault($input['opt']['gender'], '')
	)
);

$array = str_split($input['info']['fName']);
foreach($array as $char) {
	if(!preg_match("/[a-z]/i", $char)) send_error('first name is invalid. this must contain only english letters');
}

$array = str_split($input['info']['lName']);
foreach($array as $char) {
	if(!preg_match("/[a-z]/i", $char)) send_error('last name is invalid. this must contain only english letters');
}


if(!is_int($input['info']['team']) || $input['info']['team'] > 9999 || $input['info']['team'] < 1){
	send_error('team number is invalid');
}


$input['_id'] = $input['info']['fName'] . $input['info']['lName'] . '-' . $input['info']['team'];//make _id

$i = $db->user->findOne(
	array(
		'_id' => $input['_id']
	),
	array(
		"_id" => 1
	)
);//check for a duplicate _id

if(!empty($i)){
	//TODO: make a system to deal with this & probably assign usernames better
	send_error('username already taken');
}


if(strlen($input['account']['pword']) < 5){
	send_error('seriously, a ' . strlen($input['account']['pword']) . ' character password, this could be cracked in under an hour. try something more secure');
}


if(!(filter_var($input['account']['email'], FILTER_VALIDATE_EMAIL))){
	send_error('this email is not valid');
}
//TODO: check for duplicate email????


$db->user->insert($input);//finally add user

//send confirmation email + instructions for training

send_reg(array('message' => 'signup completed'));

//general functions
function checkDefault($value, $default){
	if(empty($value) == false){
		return $value;
	} else {
		return $default;
	}
}
?>