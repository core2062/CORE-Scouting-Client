<?php
//CONSIDER adding a delay to prevent multiple attempts

//set place & type (for logging)
$place = 'login.php';
$type = 'token-gen';

require 'php/init.php';

$input = $_POST['data'];
$input = json_decode($input, true);

if ($input['_id'] == "") {
	send_error("scoutid is blank");
}
if ($input['pword'] == "") {
	send_error("password is blank");
}

$user = $db->user->findOne(
	array(
		'_id' => $input['_id']
	),
	array(
		'stats' => 0
	)
);

fb($user);

if($user == ''){
	send_error("scoutID is incorrect");
}

if ($user['account']['pword'] !== $input['pword']) {
	send_error("password is incorrect");
}

if ($user['permission'] == 0) {
	send_error("your account is banned");
}

$user['token'] = uniqid("",true);
$vars['token'] = $user['token'];//for logging ... fix?

$db->user->update(
	array(
		'ip' => $vars['ip']
	),
	array(
		'$unset'=> array(
			'stats.ip' => 1,
			'token' => 1
		)
	)
);//zero out ip & token for users w/ same ip

$db->user->update(
	array(
		'_id' => $input['_id']
	),
	array(
		'$set' => array(
			'token' => $user['token'],
			'stats' => array(
				'ip' => $vars['ip'],
				'logintime' => $starttime
			)
		)
	)
);//set ip & token for user logging in


unset($user['account'], $user['stats'], $user['opt']);//remove stuff I don't want sent to browser

//regular end - can't user send_reg()
list($micro, $sec) = explode(" ",microtime());
$endtime = (float)$sec + (float)$micro;
$total_time = ($endtime - $starttime);

$db->log->insert(
	array(
		'type' => 'token-gen',
		'place' => 'login.php',
		'time' => $starttime,
		'duration' => $total_time,
		'input' => $input,
		'log' => $log,
		'vars' => $vars
	)
);

$user['message'] = 'login complete';//put message in user variable (easiest way)

ob_clean (); //empty output buffer
die(json_encode($user));
?>