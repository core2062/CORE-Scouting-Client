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
	die ("<error>ScoutID or Password is blank</error>");
}

if (mysql_result(mysql_query("SELECT COUNT(password) FROM users WHERE ScoutID='$ScoutID'"),0) == "0") {
	die ("-error-ScoutID is incorrect");	
}

if (mysql_result(mysql_query("SELECT password FROM users WHERE ScoutID='$ScoutID'"),0) != $pword){
	mysql_query ("INSERT INTO log VALUES ('Bad Password - I', NOW(), 'ScoutID: $ScoutID | Password: $pword')"); //write to log
	die("-error-Invalid ID, bitch. Your request has been logged");
}

$permission = mysql_result(mysql_query("SELECT Permission FROM users WHERE ScoutID='$ScoutID'"),0);

// 0 = account banned (only account without input)
// 1 = low account, only input access
// 2 = low account, input + scout leader access
// 3 = normal account, no direct SQL Query, no scout leader access, can access other analisis
// 8 = near admin account all access of 3 level permission + scout leader access
// 9 = admin account, full access, direct SQL Query access

if ($permission == 0) {
	die("-error-Invalid permissions, your account is banned.");
	mysql_query ("INSERT INTO log VALUES ('Bad Permissions - I', NOW(), 'ScoutID: $ScoutID')"); //write to log
}


// END OF STANDARD LOGIN


//get more parameters
$v1=$_POST["v1"];
$v2=$_POST["v2"];
$v3=$_POST["v3"];
$v4=$_POST["v4"];
$v5=$_POST["v5"];
$v6=$_POST["v6"];
$v7=$_POST["v7"];
$v8=$_POST["v8"];
$v9=$_POST["v9"];
$currentpage=$_POST["c"];

mysql_query ("INSERT INTO log VALUES ('Database I', NOW(), 'table input: $ScoutID | Current Page: $currentpage')"); //write to log

//Determine EntryID
$EntryID = mysql_query ("SELECT COUNT(EntryID) FROM entries");
$EntryID = mysql_result ($EntryID,0);

switch ($currentpage) //Build query based on current page
{
case "Regular":
	$Query = "INSERT INTO entries VALUES ('$EntryID', '$v1', '$ScoutID', '$v2', '$v3', '$v4', '$v5', '$v6', '$v7', '$v8', NOW(), 1)";
	break;
case "Human-Player":

	break;
case "Pit":

	break;
default:
	die("-error-Invalid current page, bitch");
}

mysql_query ($Query) or die('-error-didn\'t f***ing work: ' . mysql_error());

echo "1"; // Entered Sucessfully
mysql_close($link);
?>