<?php
 // remove this section before production
require_once('FirePHP/fb.php');
ob_start();

error_reporting( E_ALL );
ini_set('display_errors',1);
?>

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

//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

//add back when database is secured: include 'vars.php'; //assigns variables for DB & other sensitive info

$log = array(); //start log - used for general logging (any messages that are not recorded by anything else)


//connect to mongoDB
$m = new Mongo();
$db = $m->selectDB("CSD");


//get & validate input variables
$vars['ip'] = $_SERVER['REMOTE_ADDR'] or send_error("cannot get ip");

//variables_order must contain "C" in php.ini
$input['scoutid'] = $_COOKIE['scoutid'] or send_error("scoutid was not received");
$input['token'] = $_COOKIE['token'] or send_error("token was not received");

if ($input['scoutid'] == "") {
	send_error("scoutid is blank");
}
if ($input['token'] == "") {
	send_error("token is blank");
}


//check user & assign user object
$user = $db->execute("db.user.findOne({_id : '" . $input['scoutid'] . "'},{stats:0})");//return user object w/ no stats
fb($user);//TODO remove before production
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
$json = $_POST['data'];
if ($json == '') {
	send_error('no data sent');
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
	$db->execute("
		db.user.update({'ip':'" . $user['scoutid'] . "'}, {'\$set':{'ip':'', 'token':''}});
	"); // delete token & ip for active user
	
	//TODO check for logout error?
	
	if($error_message != ''){//if no error message is specified then assume no error
		send_reg('logout successful');
	} else {
		send_error($error_message,'','logout');
	}
}


// return functions
function send_error($error_text, $error = '', $globalError = ''){
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;
	global $user;
	
	if($globalError != ''){//if a globalError is defined, record it
		$log[] = array('globalError' => $globalError);
	}

	if ($error == ""){$error = $error_text;}

	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);

	$log_input = json_encode($input);
	$log_vars = json_encode($vars);
	$log_log = json_encode($log);
	$log_user = json_encode($user);

	$insert = "{
		type:'error',
		errorcode:'$error',
		place:'process.php',
		time:'$starttime',
		duration:'$total_time',
		input:$log_input,
		log:$log_log,
		vars:$log_vars,
		user:$log_user
	}";
	$db->execute("db.log.insert($insert)");

	ob_clean (); //empty output buffer, error_text is only thing sent
	
	if($globalError != ''){
		die("{'error':'$error_text'}");
	} else {
		die("{'error':'$error_text', 'globalError':$globalError}");
	}
	
}

function send_reg($return){
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;
	global $user;

	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);

	$log_input = json_encode($input);
	$log_vars = json_encode($vars);
	$log_log = json_encode($log);
	$log_user = json_encode($user);

	$insert = "{
		type:'regular',
		return:'$return',
		place:'process.php',
		time:'$starttime',
		duration:'$total_time',
		input:$log_input,
		log:$log_log,
		vars:$log_vars,
		user:$log_user
	}";
	$db->execute("db.log.insert($insert)");

	$return = json_encode($return);
	ob_clean (); //empty output buffer, return is only thing sent
	die($return);

}
?>