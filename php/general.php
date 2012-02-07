<?php
    // remove this section before production
    require_once('FirePHP/fb.php');
    ob_start();

    error_reporting( E_ALL );
    ini_set( 'display_errors', 1 );
?>

<?php
/*
TODO: disable catching for this response
This script handles: general functions needed for all scripts
*/

//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

//add back when database is secured: include 'vars.php';  - assigns variables for DB & other sensitive info

$log = array(); //start log - used for general logging (any messages that are not recorded by anything else)

//The User Object (full example)
$userObject = array(
	"_id"=> "SeanLang-2062",
	"permission"=> 9,
	"token"=> "4f1c860728df71.38499022",

	"info"=> array(//send
		"fName"=> "Sean",
		"lName"=> "Lang",
		"team"=> 2062
	),

	"prefs"=> array(//send, optional info
		"fade"=> true,
		"verbose"=> true
	),

	"account"=> array(//not sent, required info
		"pword"=> "superpass",
		"email"=> "slang800@gmail.com"
	),

	"stats"=> array(//not sent, created by server side
		"ip"=> "127.0.0.1",
		"logintime"=> "1327269383.167"
	),
	
	"opt"=> array(//not sent, optional info
		"zip"=> 53072,
		"browser"=> "Firefox",
		"gender"=> "m"
	)
);

//connect to mongoDB
$m = new Mongo();
$db = $m->selectDB("CSD");


//get basic variables
$vars['ip'] = $_SERVER['REMOTE_ADDR'] or send_error("cannot get ip");


//global return functions
function send_error($error_text, $error = '', $script = ''){
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;
	global $user;
	global $place;
	
	if($script != ''){//if a script is defined, record it
		$log[] = 'script defined: ' . $script;
	}

	if ($error == ""){$error = $error_text;}

	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);

	$db->log->insert(
		array(
			'type' => 'error',
			'errorcode' => $error,
			'place' => $place,
			'time' => $starttime,
			'duration' => $total_time,
			'input' => $input,
			'log' => $log,
			'vars' => $vars,
			'user' => $user
		)
	);

	//TODO: enable below before production
	ob_clean (); //empty output buffer, error_text is only thing sent
	
	if($script == ''){
		die("{'error':'$error_text'}");
	} else {
		die("{'error':'$error_text', 'script':'$script'}");
	}
}

function send_reg($return = ''){
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;
	global $user;
	global $type;
	global $place;

	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);

	$db->log->insert(
		array(
			'type' => $type,
			'return' => $return,
			'place' => $place,
			'time' => $starttime,
			'duration' => $total_time,
			'input' => $input,
			'log' => $log,
			'vars' => $vars,
			'user' => $user
		)
	);

	$return = json_encode($return);

	ob_clean (); //empty output buffer, return is only thing sent
	die($return);
}
?>