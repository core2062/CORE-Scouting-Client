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

//start log var
$log = "";

$m = new Mongo(); // connect
$db = $m->selectDB("CSD");

//variables_order must contain "C" in php.ini
$scoutid = $_COOKIE['scoutid'] or send_error("scoutid not in cookie","");
$pword_input = $_COOKIE['pword'] or send_error("pword not in cookie","");
$ip=$_SERVER['REMOTE_ADDR'] or send_error("cannot get ip","");

fb($scoutid);
fb($pword_input);
fb($ip);

$pword = $db->execute("db.user.findOne({_id : '$scoutid'},{_id:0, pword:1})");
$pword = $pword["retval"]["pword"];

if ($pword == "") {
	//return bad scoutid
}
if ($pword !== $pword_input) {
	//return bad pword
}

$token = uniqid("",true);

//regular end
list($micro, $sec) = explode(" ",microtime());
$endtime = (float)$sec + (float)$micro;
$total_time = ($endtime - $starttime);

$input = "{type:'token-gen]', scoutid:'$scoutid', time:'$starttime', duration:'$total_time', place:'login.php', token:'$token', ip:'$ip', log:'$log'}";
$db->execute("db.log.insert($input)");

ob_clean (); //empty output buffer, error_text is only thing sent

die("{token:'$token'}");

function send_error($error_text, $error) {
	global $db;
	global $starttime;
	global $log;
	global $scoutid;

	if ($error == "") {$error = $error_text;}
	
	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);
	
	$input = "{type:'error', scoutid:'$scoutid', time:'$starttime', duration:'$total_time', place:'login.php', errorcode:'$error', ip:'$ip', log:'$log'}";
	$db->execute("db.log.insert($input)");
	

	
	ob_clean (); //empty output buffer, error_text is only thing sent
	die("{error:'$error_text'}");
}
?>