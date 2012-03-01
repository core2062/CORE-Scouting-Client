<?php
	// remove this section before production
	require_once('FirePHP/fb.php');
	ob_start();

	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );
?>
<?php
//This script handles: general functions needed for almost all scripts

//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

//add back when database is secured: include 'vars.php';  - assigns variables for DB & other sensitive info (not put on github)

//connect to mongoDB
$m = new Mongo();
$db = $m->selectDB("csd");

//get basic variables
$vars['ip'] = $_SERVER['REMOTE_ADDR'] or send_error("cannot get ip");

//The User Object (full example) -- referenced used in login script, and maybe others too later
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
		"logintime"=> 1327269383.167
	),
	
	"opt"=> array(//not sent, optional info
		"zip"=> 53072,
		"browser"=> "Firefox",
		"gender"=> "m"
	)
);

//logging function
$log = array(); //start log - used for general logging (any messages that are not recorded by anything else)

//TODO: allow log to writeout to DB before end of script
function logger($message, $fbDisplay = false){
	global $log;
	global $starttime;

	list($micro, $sec) = explode(" ",microtime());

	$log[] = array($message, (float)$sec + (float)$micro - $starttime);

	if($fbDisplay == true){
		fb($message);
	}
}

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

	logger(ob_get_contents());

	$db->log->insert(
		array(
			'type' => 'error',
			'errorcode' => $error,
			'place' => $place,
			'time' => $starttime,
			'input' => $input,
			'log' => $log,
			'vars' => $vars,
			'user' => $user
		)
	);

	ob_clean (); //empty output buffer, stuff below is only thing sent
	
	if($script == ''){
		die("{'error':'$error_text'}");
	} else {
		die("{'error':'$error_text', 'script':'$script'}");
	}
}

function send_reg($return = '',$enableEncode = true, $logReturn = true){
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;
	global $user;
	global $type;
	global $place;

	logger(ob_get_contents());

	$db->log->insert(
		array(
			'type' => $type,
			'return' => $logReturn ? $return : "",
			'place' => $place,
			'time' => $starttime,
			'input' => $input,
			'log' => $log,
			'vars' => $vars,
			'user' => $user
		)
	);

	if($enableEncode == true){//option required for index.php (sends entire page as return)
		$return = json_encode($return);
	}
	
	ob_clean (); //empty output buffer, stuff below is only thing sent
	die($return);
}

//PATH is (P)HP (A)rrays (T)o (H)TML

$selfClosingTags = array('img','br');

function path($array){
	if(!is_array($array)){//temporary support for converting text to array to support non-PHP 5.4
		//TODO: add this w/ regex
	}

	if(isset($innerHTML)){
		echo "\n ---echo: " . $innerHTML;
	}

	//add stuff for self closing tags
	$tagName = $array[0];
	unset($array[0]);

	//make a way to manually specify a self closing tag
	$selfClosing = in_array($tagName, $selfClosingTags);

	$return = "<" . $tagName;
	$key = 1;
	$innerHTML = "";
	while (array_key_exists($key, $array)){
		if(is_array($array[$key])){
			$innerHTML .= path($array[$key]);
		} else {
			$innerHTML .= $array[$key];
		}
		unset($array[$key]);
		$key++;
	}

	foreach($array as $key => $value){
		$return .= ' ' . $key . '="' . $value . '"';
	}
	//add stuff for self closing tags
	$return .= ">" . $innerHTML . "</" . $tagName . ">";

	return $return;
}
?>