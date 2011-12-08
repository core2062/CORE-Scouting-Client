<?php
 // remove this section before production
require_once('FirePHP/fb.php');
ob_start();

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
?>

<?php
//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

//add back when database is secured: include 'vars.php'; //assigns variables for DB & other sensitive info

$log=""; //start log

$m = new Mongo(); // connect
$db = $m->selectDB("CSD");

//variables_order must contain "C" in php.ini
$input['scoutid'] = $_COOKIE['scoutid'] or send_error("scoutid was not received","");
$input['token'] = $_COOKIE['token'] or send_error("token was not received","");
$vars['ip'] = $_SERVER['REMOTE_ADDR'] or send_error("cannot get ip","");

if ($input['scoutid'] == "") {
	send_error("scoutid is blank","");
}
if ($input['token'] == "") {
	send_error("token is blank","");
}

$user = $db->execute("db.user.findOne({_id : '" . $input['scoutid'] . "'},{stats:0})");
fb($user);
$user = $user['retval'];

if ($user['token'] !== $input['token']) {
	//TODO make this send a logout function
	send_error("token is incorrect","");
}

if ($user['ip'] !== $vars['ip']) {
	//TODO make this send a logout function
	send_error("ip is incorrect","");
}

$json = $_POST['data'];
if ($json == '') {
	send_error('no data sent','');
}

$input = array_merge(json_decode($json, true),$input);

die();



// 0 = account banned, can't do anything
// 1 = low account, only input access
// 2 = low account, input + scout leader access
// 3 = normal account, no direct SQL Query, no scout leader access, can access other analisis
// 8 = near admin account all access of 3 level permission + scout leader access
// 9 = admin account, full access, all DB access

if ($user['permission'] == 0) {
	send_error('Your Account Is Banned', ' | Banned Account');
}

switch ($input['request']) {
case "Poll": //for match signup, mail poll, other?

	send_error('This part is not finished','');

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
case "Input": 

	send_error('This part is not finished','');
	include 'php/input.php';

break;
case "Mail":

	send_error('This part is not finished','');
	include 'php/mail.php';

break;
case "Query":
	if ($permission < 3) {
		send_error( 'Invalid Permissions','');
	}
	
	//get more parameters
	//$QueryDirty=$_POST["q"];
	//$type=$_POST["t"];
	//$var1=$_POST["v1"];
	//$place=$_POST["p"];
	
	// clean parameters
	//$Query = mysql_real_escape_string($QueryDirty);
	//$type = mysql_real_escape_string($type);
	//$var1 = mysql_real_escape_string($var1);
	
	//mysql_query ("INSERT INTO log VALUES ('Database Q', NOW(), 'ScoutID: $ScoutID | table query: $Query | type: $type | var1: $var1')"); //write to log
	
	switch ($Query)
	{
	case 1: //returns table of all database entries
		$Query = "SELECT * FROM entries";
		break;
	case 2: //finds number of matches
		$Query = "SELECT COUNT(*) FROM entries WHERE TeamNum=$var1";
		break;
	case 3: //handles match signup
		$Query = "";
		break;
	case 4: //handles scout leader access
	
		$Query = "";
		break;
	default:
		if ($permission < 9) {
			send_error('invalid permissions - admin only','');
		};
		//$Query = $QueryDirty;
	};
	
		
	if ($type=="table")
	{
		//table formatter used to be here, send json rather than html
	}
	
	
	elseif($type=="value") {
	$value = mysql_query($Query);
	$return_text.="<queryreturn place=\"$place\">" . mysql_result($value,0) . "</queryreturn>";
	}
	else send_error('Invalid Request Type','');
break;
case "Admin":
	if ($permission < 9) {
		send_error('invalid permissions - admin only','');
	}
	
	//Errorcheck
	//Recalculate
	//Reset
	
break;
case "Logout":

	// delete token & ip for active user

break;
default:
	send_error('invalid request type','');
}





function send_error($error_text, $error) {
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;
	global $user;

	if ($error == "") {$error = $error_text;}
	
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
	die("{'error':'$error_text'}");
}

function send_reg() {
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;
	global $user;
	global $return;
	
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