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

//start log var
$log = "";

$m = new Mongo(); // connect
$db = $m->selectDB("CSD");

//variables_order must contain "C" in php.ini
$json = $_POST['data'];
$json = json_decode($json, true);

$scoutid = $json["scoutid"] or send_error("scoutid was not received","");
$pword_input = $json["pword"] or send_error("password was not received","");
$ip=$_SERVER['REMOTE_ADDR'] or send_error("cannot get ip","");

fb($scoutid);
fb($pword_input);
fb($ip);

if ($pword_input == "") {
	send_error("password is blank","");
}
if ($scoutid == "") {
	send_error("scoutid is blank","");
}

$pword = $db->execute("db.user.findOne({_id : '$scoutid'},{_id:0, pword:1})");

if ($pword['retval'] == '') {
	send_error("scoutid is incorrect","");
}

$pword = $pword["retval"]["pword"];

fb($pword);

if ($pword !== $pword_input) {
	send_error("password is incorrect","");
}

$token = uniqid("",true);
$db->execute("
	db.user.update({_id : '$scoutid'}, {'\$set':{'token':'$token'}});
	db.user.update({'ip':'$ip'}, {'\$set':{'ip':'', 'token':''}});
	db.user.update({_id : '$scoutid'}, {'\$set':{'ip':'$ip'}});
");

//regular end
list($micro, $sec) = explode(" ",microtime());
$endtime = (float)$sec + (float)$micro;
$total_time = ($endtime - $starttime);

$input = "{type:'token-gen', scoutid:'$scoutid', time:'$starttime', duration:'$total_time', place:'login.php', token:'$token', ip:'$ip', log:'$log'}";
$db->execute("db.log.insert($input)");

ob_clean (); //empty output buffer, error_text is only thing sent

die("{'token':'$token'}");




function send_error($error_text, $error) {
	global $db;
	global $starttime;
	global $log;
	global $scoutid;
	global $ip;

	if ($error == "") {$error = $error_text;}
	
	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);
	
	$input = "{type:'error', scoutid:'$scoutid', time:'$starttime', duration:'$total_time', place:'login.php', errorcode:'$error', ip:'$ip', log:'$log'}";
	$db->execute("db.log.insert($input)");
	

	
	//ob_clean (); //empty output buffer, error_text is only thing sent
	die("{'error':'$error_text'}");
}
?>