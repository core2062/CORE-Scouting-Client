<?php
/*

This script handles:
	validation for logged in users (based on tokens)
	getting the user object
	checking permissions
	pointing to the correct script depending on the request type
	general use functions
	and error/success logging

Permission Levels:
	0 = banned : can't do anything
	1 = scout : input access
	2 = low scout-leader : access scout-leader stuff, & level 1 permissions
	3 = analyzer : access data from analysis, download data for team, & level 1 permissions
	4 = high scout-leader : level 1-3 permissions
	5 =
	6 =
	7 =
	8 =
	9 = admin : everything
*/

//set place & type (for logging)
$place = 'process.php';
$type = 'regular';

require 'php/general.php';

//get & validate input variables
//variables_order must contain "C" in php.ini
$input = $_COOKIE['user'] or logout("user object was not received");
$input = json_decode($input, true);

if (empty($input['_id']) == true) {
	send_error("scoutid was not receved",'','logout();');
}
if (empty($input['token']) == true) {
	logout("token was not receved");
}

//check user & assign user object
$user = $db->execute("db.user.findOne({_id : '" . $input['_id'] . "'},{stats:0})");//return user object w/ no stats
$user = $user['retval'];//strip away extra stuff from mongoDB

if ($user['token'] !== $input['token']) {//validate token
	logout("token is incorrect, you have been logged out for security reasons");
}
if ($user['ip'] !== $vars['ip']) {//validate ip address
	logout("ip is incorrect, you have been logged out for security reasons");
}
if ($user['permission'] == 0) {
	send_error('your account is banned', 'banned account');
}

//get request data from post
$json = $_POST['data'] or send_error('no data sent');

if (empty($json) == true || $json == "undefined") {//post can send the string undefined if given no data... really gay
	send_error('no data received');
}

$input = array_merge(json_decode($json, true), $input);// add json request data to input object (for logging & organization)

// request switch
switch ($input['request']) {
case "poll": //for match signup, mail poll, other?

	send_error('this part is not finished');

	//get more parameters
//	$checksignup=$_POST["s"]; // =1 for yes, =0 for no
//	$competition=$_POST["c"];
//	$matchnum=$_POST["m"];

	// clean parameters
//	$checksignup = mysql_real_escape_string($checksignup);
//	$competition = mysql_real_escape_string($competition);
//	$matchnum = mysql_real_escape_string($matchnum);


	// BEGIN MESSAGE POLL


	// check for mail


	//delete message after getting it


	// END MESSAGE POLL
	// BEGIN MATCH SIGNUP POLL

	if ($checksignup == 1) {
		//check if team leader
		//check for who has signed up for match
	}

break;
case "input":
	//user is not banned based on above check
	//therefore user has needed permissions

	include 'php/input.php';

break;
case "mail":

	send_error('this part is not finished');
	include 'php/mail.php';
	
	//encode chars to remove HTML or JS
	
break;
case "query":
	if ($user['permission'] < 3) {
		send_error('Invalid Permissions');
	}

	send_error('this part is not finished');
	include 'php/query.php';


break;
case "scout-leader":
	if ($user['permission'] != 2 && $user['permission'] != 4) {
		send_error('invalid permissions - scout-leader only','');
	}

	send_error('this part is not finished');
	include 'php/admin.php';

break;
case "admin":
	if ($user['permission'] < 9) {
		send_error('invalid permissions - admin only');
	}

	send_error('this part is not finished');
	include 'php/admin.php';

break;
case "logout":

	logout();

break;
default:
	send_error('invalid request type');
}


//generic functions
function logout($error_message = ''){//must be function to let it be called from other areas in script
	global $db;
	global $user;
	
	$db->execute("db.user.update({_id:'" . $user['_id'] . "'}, {'\$unset':{ip:1, token:1}});"); // delete token & ip for active user
	
	//TODO check for logout error?
	
	if($error_message == ''){//if no error message is specified then assume no error
		send_reg(array('message' => 'logout successful'));
	} else {
		send_error($error_message,'','logout();');
	}
}
?>