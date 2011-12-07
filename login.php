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

$vars['ip'] = $_SERVER['REMOTE_ADDR'] or send_error("cannot get ip","");

if ($input['scoutid'] == "") {
	send_error("scoutid is blank","");
}
if ($input['pword'] == "") {
	send_error("password is blank","");
}

$pword = $db->execute("db.user.findOne({_id : '" . $input['scoutid'] . "'},{_id:0, pword:1})");

if ($pword['retval'] == '') {
	send_error("scoutid is incorrect","");
}

$pword = $pword["retval"]["pword"];

if ($pword !== $input['pword']) {
	send_error("password is incorrect","");
}

$vars['token'] = uniqid("",true);
$db->execute("
	db.user.update({'ip':'" . $vars['ip'] . "'}, {'\$set':{'ip':'', 'token':''}});
	db.user.update({_id : '" . $input['scoutid'] . "'}, {'\$set':{'token':'" . $vars['token'] . "', 'ip':'" . $vars['ip'] . "', 'logintime':'$starttime'}});
");

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

ob_clean (); //empty output buffer, error_text is only thing sent
die("{'token':'" . $vars['token'] . "'}");




function send_error($error_text, $error) {
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;

	if ($error == "") {$error = $error_text;}
	
	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);
	
	$log_input = json_encode($input);
	$log_vars = json_encode($vars);
	$log_log = json_encode($log);
	
	$insert = "{
		type:'error',
		errorcode:'$error',
		place:'login.php',
		time:'$starttime',
		duration:'$total_time',
		input:$log_input,
		log:$log_log,
		vars:$log_vars
	}";
	$db->execute("db.log.insert($insert)");
	
	ob_clean (); //empty output buffer, error_text is only thing sent
	die("{'error':'$error_text'}");
}
?>