<?php
//CONSIDER adding a delay to prevent multiple attempts

//set place & type (for logging)
$place = 'login.php';
$type = 'token-gen';

require 'php/general.php';

$input = $_POST['data'];
$input = json_decode($input, true);

if ($input['_id'] == "") {
	send_error("scoutid is blank");
}
if ($input['pword'] == "") {
	send_error("password is blank");
}

$user = $db->execute("db.user.findOne({_id : '" . $input['_id'] . "'},{stats:0})");

if ($user['retval'] == '') {
	send_error("scoutID is incorrect");
}

$user = $user['retval'];

if ($user['account']['pword'] !== $input['pword']) {
	send_error("password is incorrect");
}

if ($user['permission'] == 0) {
	send_error("your account is banned");
}

$user['token'] = uniqid("",true);
$vars['token'] = $user['token'];//for logging ... fix?

$db->execute("
	db.user.update({'ip':'" . $vars['ip'] . "'}, {'\$set':{'stats':'', 'token':''}});
	db.user.update({_id : '" . $input['_id'] . "'}, {'\$set':{'token':'" . $user['token'] . "', 'stats':{'ip':'" . $vars['ip'] . "', 'logintime':'" . $starttime . "'}}});
"); //first zero out ip & token for users w/ same ip then set ip & token for user logging in


unset($user['account'], $user['stats'], $user['opt']);//remove stuff I don't want sent to browser

//regular end - can't user send_reg()
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

$user['message'] = 'login complete';//put message in user variable (easiest way)

ob_clean (); //empty output buffer
die(json_encode($user));
?>