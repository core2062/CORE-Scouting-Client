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

//array_keys()    array array_keys ( array $input [, mixed $search_value [, bool $strict = false ]] )
//array_merge_recursive()     array array_merge_recursive ( array $array1 [, array $... ] )

$objects = array('info', 'account');

$required = array(
	array('info', 'fName'),
	array('info', 'lName'),
	array('info', 'email')
);

$len = sizeof($required);
for(var $i = 0; i < $len; $i++){
	if (empty($input[ $required[$i][1] ][ $required[$i][2] ]) == true){
		send_error($required[$i] . ' wasn\'t not sent');
	}
}



//check for same username
//check for same email
//check for invalid team, email, name... (also on client side)

$input['permission'] = 1;//TODO make system to assign higher permissions automatically

$db->execute("
	db.user.insert(" . json_encode($input) . ");
");

//send confirmation email + instructions for training

/*

The User Object (full example)
{
	"_id": "SeanLang-2062",
	"permission": 9,
	"token": "4f1c860728df71.38499022",

	"info": {//send
		"fName": "Sean",
		"lName": "Lang",
		"team": 2062
	},

	"prefs": {//send, optional info
		"fade": true,
		"verbose": true
	},

	"account": {//not sent, required info
		"pword": "superpass",
		"email": "slang800@gmail.com"
	},

	"stats": {//not sent, created by server side
		"ip": "127.0.0.1",
		"logintime": "1327269383.167"
	}
	
	"opt": {//not sent, optional info
		"zip": 53072,
		"browser": "Firefox",
		"gender": "m"
	},
}

*/
?>