<?php
/*
TODO add openid based sign-up (perhaps a accordian to hold different sign-up methods)

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

//primary error correction is done client-side, error messages from this are not friendly

//check for missing required data (for optional, assign default)

//build list of required inputs
$objects = array('info', 'account');
$required = array();//intalize empty

$len = sizeof($objects);
for($i = 0; $i < $len; $i++){
	$e = array_keys($userObject[$objects[$i]]);
	$len2 = sizeof($e);
	for($f = 0; $f < $len2; $f++){
		$e[$f] = array($objects[$i], $e[$f]);
	}
	$required = array_merge($required, $e);
}
	fb($required);
	fb($input);
//check everything that's required
$len = sizeof($required);
for($i = 0; $i < $len; $i++){
	if (empty($input[ $required[$i][0] ][ $required[$i][1] ]) == true){
		send_error($required[$i][1] . ' wasn\'t not sent');
	}
}
die();


//check for same username
//check for same email
//check for invalid team, email, name... (also on client side)

$input['permission'] = 1;//TODO make system to assign higher permissions automatically

$db->execute("
	db.user.insert(" . json_encode($input) . ");
");

//send confirmation email + instructions for training
?>