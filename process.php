<?php
//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

//get basic parameters
$ScoutID=$_POST["s"];
$pword=$_POST["pw"];
$Request=$_POST["rt"]; // I = Input	Q = Query	A = Admin	P = Poll	M = Mail

include 'variables.php'; //assigns varables for $mysql_server, $mysql_user, $mysql_pasword

$log=" | Request: $Request"; //start log

$link = mysql_connect($mysql_server,$mysql_user,$mysql_pasword) or send_error('Could Not Connect', 'Could Not Connect: ' . mysql_error()); //build MySQL Link
mysql_select_db('test') or send_error('Could Not Select Database',''); //select database

if ($ScoutID == "" || $pword == "") {
	send_error('ScoutID Or Password Is Blank','');
}

// clean parameters
$ScoutID = mysql_real_escape_string($ScoutID);
$pword = mysql_real_escape_string($pword);
$Request = mysql_real_escape_string($Request);

if (mysql_result(mysql_query("SELECT COUNT(ScoutID) FROM users WHERE ScoutID='$ScoutID'"),0) == "0") {
	send_error('ScoutID Is Incorrect', ' | Bad ScoutID | Password: '.$pword);
}

if (mysql_result(mysql_query("SELECT password FROM users WHERE ScoutID='$ScoutID'"),0) != $pword){
	send_error('Invalid Password', ' | Bad Password | Password: '.$pword);
}

$permission = mysql_result(mysql_query("SELECT Permission FROM users WHERE ScoutID='$ScoutID'"),0);

// 0 = account banned, can't do anything
// 1 = low account, only input access
// 2 = low account, input + scout leader access
// 3 = normal account, no direct SQL Query, no scout leader access, can access other analisis
// 8 = near admin account all access of 3 level permission + scout leader access
// 9 = admin account, full access, direct SQL Query access

if ($permission == 0) {
	send_error('Your Account Is Banned', ' | Banned Account');
}

$return_text = "<root>"; //start return text

switch ($Request) {
case "P": // Poll

	send_error('This part is not finished','');

	//get more parameters
	$checksignup=$_POST["s"]; // =1 for yes, =0 for no
	$competition=$_POST["c"];
	$matchnum=$_POST["m"];
	
	// clean parameters
	$checksignup = mysql_real_escape_string($checksignup);
	$competition = mysql_real_escape_string($competition);
	$matchnum= mysql_real_escape_string($matchnum);
	
	
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
case "I": // Input 

	send_error('This part is not finished','');
	include 'php/input.php';

break;
case "M": // Mail

	send_error('This part is not finished','');
	include 'php/mail.php';

break;
case "Q": // Query
	if ($permission < 3) {
		send_error( 'Invalid Permissions', ' | Bad Permissions | Permissions: '. $permission);
	}
	
	//get more parameters
	$QueryDirty=$_POST["q"];
	$type=$_POST["t"];
	$var1=$_POST["v1"];
	$place=$_POST["p"];
	
	// clean parameters
	$Query = mysql_real_escape_string($QueryDirty);
	$type = mysql_real_escape_string($type);
	$var1 = mysql_real_escape_string($var1);
	
	mysql_query ("INSERT INTO log VALUES ('Database Q', NOW(), 'ScoutID: $ScoutID | table query: $Query | type: $type | var1: $var1')"); //write to log ---------------------- REMOVE
	
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
		send_error('Invalid Permissions', ' | Bad Permissions - Admin Only | Permissions: '.$permission);
		};
		$Query = $QueryDirty;
	};
	
		
	if ($type=="table")
	{
	$return_text.="<queryreturn place=\"$place\"><![CDATA[ "; 
	$return_text.="<table border='1' style=\"border-collapse:collapse; margin:10px; width:95%;\" class=\"tablesorter\" id=\"QueryTable\"><thead>"; //Open HTML Table
	$Result = mysql_query($Query); //Execute the query

		//Header Row with Field Names
		$NumFields = mysql_num_fields($Result);
		$return_text.= "<tr>";
		for ($i=0; $i < $NumFields; $i++)
		{     
		$return_text.= "<th>" . mysql_field_name($Result, $i) . "</th>"; 
		}
		$return_text.= "</tr></thead><tbody>";
		//Loop thru results
		$RowCt = 0; //Row Counter
		while($Row = mysql_fetch_assoc($Result))
		{
		$return_text.= "<tr>";
		//Loop thru each field
		foreach($Row as $field => $value)
		{
		$return_text.="<td>$value</td>";
		}
		$return_text.="</tr>";
		}
	
	$return_text.="</tbody></table> ]]></queryreturn>";
	$return_text.="<queryreturn place=\"resultnum\">" . mysql_num_rows($Result) . "</queryreturn>";
	}
	
	
	elseif($type=="value") {
	$value = mysql_query($Query);
	$return_text.="<queryreturn place=\"$place\">" . mysql_result($value,0) . "</queryreturn>";
	}
	else send_error('Invalid Request Type','');
break;
case "A": // Admin
	if ($permission < 9) {
		send_error('Invalid Permissions',' | Invalid Permissions - Admin Only | Permissions');
	}


break;
default:
	send_error('Invalid Request Type','');
}


function send_error($error_text, $error) {
	global $starttime;
	global $log;
	global $ScoutID;
	
	$log.= $error;
	if ($error == "") {$log.= " | " . $error_text;}
	
	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);
	
	mysql_query ("INSERT INTO log VALUES ('$ScoutID', NOW(), '[Error]$log |', '$total_time')"); //write to log
	ob_clean (); //empty output buffer, error_text is only thing sent
	die("<error>$error_text</error>");
}
function send_reg() {
	global $starttime;
	global $log;
	global $ScoutID;
	
	global $return_text;
	
	list($micro, $sec) = explode(" ",microtime());
	$endtime = (float)$sec + (float)$micro;
	$total_time = ($endtime - $starttime);
	
	mysql_query ("INSERT INTO log VALUES ('$ScoutID', NOW(), '[Success]$log |', '$total_time')"); //write to log
	die("$return_text</root>");
}
?>