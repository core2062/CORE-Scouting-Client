<?php
//get basic parameters
$ScoutID=$_POST["s"];
$pword=$_POST["p"];

$link = mysql_connect("mysql2.000webhost.com","a7984559_scouter","Crutr7tusakus2aWacUF") or die('Could not connect: ' . mysql_error());      //build MySQL Link
mysql_select_db('a7984559_scout') or die('Could not select database'); //select database

// clean parameters
$ScoutID = mysql_real_escape_string($ScoutID);
$pword = mysql_real_escape_string($pword);

if ($ScoutID == "" || $pword == "") {
	die ("-error-ScoutID or Password is blank");
}

if (mysql_result(mysql_query("SELECT COUNT(password) FROM users WHERE ScoutID='$ScoutID'"),0) == "0") {
	die ("-error-ScoutID is incorrect");	
}

if (mysql_result(mysql_query("SELECT password FROM users WHERE ScoutID='$ScoutID'"),0) != $pword){
	mysql_query ("INSERT INTO log VALUES ('Bad Password - A', NOW(), 'ScoutID: $ScoutID | Password: $pword')"); //write to log
	die("-error-Invalid ID, bitch. Your request has been logged");
}

$permission = mysql_result(mysql_query("SELECT Permission FROM users WHERE ScoutID='$ScoutID'"),0);

// 0 = account banned (only account without input)
// 1 = low account, only input access
// 2 = low account, input + scout leader access
// 3 = normal account, no direct SQL Query, no scout leader access, can access other analisis
// 8 = near admin account all access of 3 level permission + scout leader access
// 9 = admin account, full access, direct SQL Query access

if ($permission < 9) {
	die("-error-Invalid permissions");
	mysql_query ("INSERT INTO log VALUES ('Bad Permissions - A', NOW(), 'ScoutID: $ScoutID')"); //write to log
}


// END OF STANDARD LOGIN


//get more parameters
$AdminQuery=$_POST["q"];
$var1=$_POST["v1"];


switch ($AdminQuery)
{
case 1: //error check
	$Query = "SELECT * FROM entries";
	break;
case 2: //reset database (determined by var1)
	$Query = "SELECT COUNT(*) FROM entries WHERE TeamNum=$var1";
	break;
case 3: //refresh calculation databases (like OPR and DD)
	$Query = "";
	break;
default:
	die('-error-Switch Error');
};


?>