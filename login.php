<?php
 // remove this section before production
require_once('FirePHP/fb.php');
ob_start();

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
?>

<?php
// TODO finish token based login

//variables_order must contain "C" in php.ini
$scoutid = $_COOKIE['scoutid'] or die("error");
$pword_input = $_COOKIE['pword'] or die("error");
$ip=$_SERVER['REMOTE_ADDR'] or die("error");

fb($scoutid);
fb($pword_input);
fb($ip);

$m = new Mongo(); // connect
$db = $m->selectDB("CSD");

$pword = $db->execute("db.user.findOne({_id : '$scoutid'},{_id:0, pword:1})");
$pword = $pword["retval"]["pword"];

if ($pword == "") {
	//return bad scoutid
}
if ($pword !== $pword_input) {
	//return bad pword
}


fb($pword);

$token = uniqid("",true);
fb($token);

// db.user.find({scoutid : "SeanLang-2062"},{_id:0, email:1, permission:1})
//$response = $db->execute("function(greeting, name) { return greeting+', '+name+'!'; }", array("Good bye", "Joe"));
//echo $response['retval'];


function send_error($error_text, $error) {
	global $starttime;
	global $log;
	global $scoutid;
	
	$log.= $error;
	if ($error == "") {$log.= " | " . $error_text;}
	
	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);
	
	ob_clean (); //empty output buffer, error_text is only thing sent

}
function send_reg() {
	global $starttime;
	global $log;
	global $scoutid;
	
	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);
	
}
?>