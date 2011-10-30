<?php


$link = mysql_connect("localhost","test","test") or send_error('Could Not Connect', 'Could not connect: ' . mysql_error()); //build MySQL Link
mysql_select_db('test') or send_error('Could Not Select Database',''); //select database



//send message to user from me

function send_error($error_text, $error_log) {
	global $starttime;
	if ($error_log == "") {$error_log = $error_text;}
	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime); 
	mysql_query ("INSERT INTO log VALUES ('$ScoutID', NOW(), '$error_log', '$total_time')"); //write to log
	die("<error>$error_text</error>");
}
function send_reg() {
	global $return_text;
	mysql_query ("INSERT INTO log VALUES ('$ScoutID', NOW(), '$error_log', '$total_time')"); //write to log
	die("<root>$return_text</root>");
}
?>