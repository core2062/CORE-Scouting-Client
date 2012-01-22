<?php
 // remove this section before production
require_once('FirePHP/fb.php');
ob_start();

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
?>

<?php

//TODO disable catching for this response

//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

//TODO fix logging & remove below at that time
$log = '';

$m = new Mongo(); // connect
$db = $m->selectDB("CSD");

$input = $_POST['data'];
$input = json_decode($input, true);

$vars['ip'] = $_SERVER['REMOTE_ADDR'] or send_error("cannot get ip");

if ($input['scoutid'] == "") {
	send_error("scoutid is blank");
}
if ($input['pword'] == "") {
	send_error("password is blank");
}

$user = $db->execute("db.user.findOne({_id : '" . $input['scoutid'] . "'},{stats:0})");

if ($user['retval'] == '') {
	send_error("scoutID is incorrect");
}

$user = $user['retval'];

if ($user['pword'] !== $input['pword']) {
	send_error("password is incorrect");
}

if ($user['permission'] == 0) {
	send_error("your account is banned");
}

$vars['token'] = uniqid("",true);
$db->execute("
	db.user.update({'ip':'" . $vars['ip'] . "'}, {'\$set':{'ip':'', 'token':''}});
	db.user.update({_id : '" . $input['scoutid'] . "'}, {'\$set':{'token':'" . $vars['token'] . "', 'ip':'" . $vars['ip'] . "', 'logintime':'$starttime'}});
"); //first zero out ip & token for users w/ same ip then set ip & token for user logging in


unset($user['pword'], $user['ip'], $user['info']['zip'], $user['info']['browser']);//remove stuff I don't want sent to browser

//regular end
list($micro, $sec) = explode(" ",microtime());
$endtime = (float)$sec + (float)$micro;
$total_time = ($endtime - $starttime);

//add log_ prefix to be able to use origionals in end function
$log_input = json_encode($input);
$log_vars = json_encode($vars);
$log_log = json_encode($log);

$insert = "{
	type:'token-gen',
	place:'login.php',
	time:'$starttime',
	duration:'$total_time',
	input:$log_input,
	log:$log_log,
	vars:$log_vars
}";
$db->execute("db.log.insert($insert)");

ob_clean (); //empty output buffer
die(json_encode($user));


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
		place:'login.php',
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
?>