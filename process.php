<?php
//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

//get basic parameters
$ScoutID=$_POST["s"];
$pword=$_POST["pw"];
$RequestType=$_POST["rt"]; // I = Input	Q = Query	A = Admin	P = Poll	M = Mail

$link = mysql_connect("localhost","test","test") or send_error('Could Not Connect', 'Could not connect: ' . mysql_error()); //build MySQL Link
mysql_select_db('test') or send_error('Could Not Select Database',''); //select database

if ($ScoutID == "" || $pword == "") {
	send_error('ScoutID Or Password Is Blank','');
}

// clean parameters
$ScoutID = mysql_real_escape_string($ScoutID);
$pword = mysql_real_escape_string($pword);
$RequestType = mysql_real_escape_string($RequestType);

if (mysql_result(mysql_query("SELECT COUNT(ScoutID) FROM users WHERE ScoutID='$ScoutID'"),0) == "0") {
	send_error('ScoutID Is Incorrect', 'Bad ScoutID - '.$RequestType.' | Password: '.$pword);
}

if (mysql_result(mysql_query("SELECT password FROM users WHERE ScoutID='$ScoutID'"),0) != $pword){
	send_error('Invalid Password', 'Bad Password - '.$RequestType.' | Password: '.$pword);
}

$permission = mysql_result(mysql_query("SELECT Permission FROM users WHERE ScoutID='$ScoutID'"),0);

// 0 = account banned, can't do anything
// 1 = low account, only input access
// 2 = low account, input + scout leader access
// 3 = normal account, no direct SQL Query, no scout leader access, can access other analisis
// 8 = near admin account all access of 3 level permission + scout leader access
// 9 = admin account, full access, direct SQL Query access

if ($permission == 0) {
	send_error( 'Your Account Is Banned', 'Banned Account - '.$RequestType.' | Permissions: '.$permission);
}

switch ($RequestType) {
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
		
	}



break;
case "I": // Input 

	send_error('This part is not finished','');

break;
case "M": // Mail

	send_error('This part is not finished','');

break;
case "Q": // Query
	if ($permission < 3) {
		send_error( 'Invalid Permissions', 'Bad Permissions - Q | Permissions: '. $permission);
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
		if ($permission < 9)
		{
		send_error('Invalid Permissions', 'Bad Permissions - AQ | Permissions: '.$permission);
		};
		$Query = $QueryDirty;
	};
	
		
	if ($type=="table")
	{
	$Table = "<queryreturn place=\"$place\"><![CDATA[ "; 
	$Table.= "<table border='1' style=\"border-collapse:collapse; margin:10px; width:95%;\" class=\"tablesorter\" id=\"QueryTable\"><thead>"; //Open HTML Table
	$Result = mysql_query($Query); //Execute the query

		//Header Row with Field Names
		$NumFields = mysql_num_fields($Result);
		$Table.= "<tr>";
		for ($i=0; $i < $NumFields; $i++)
		{     
		$Table.= "<th>" . mysql_field_name($Result, $i) . "</th>"; 
		}
		$Table.= "</tr></thead><tbody>";
		//Loop thru results
		$RowCt = 0; //Row Counter
		while($Row = mysql_fetch_assoc($Result))
		{
		$Table.= "<tr>";
		//Loop thru each field
		foreach($Row as $field => $value)
		{
		$Table.= "<td>$value</td>";
		}
		$Table.= "</tr>";
		}
	
	$Table.= "</tbody></table> ]]></queryreturn>";
	echo $Table;
	echo "<queryreturn place=\"resultnum\">" . mysql_num_rows($Result) . "</queryreturn>";
	}
	
	
	elseif($type=="value") {
	$value = mysql_query($Query);
	echo "<queryreturn place=\"$place\">" . mysql_result($value,0) . "</queryreturn></root>";
	}
	else send_error('Invalid Request Type','');
break;
case "A": // Admin
	if ($permission < 9) {
		send_error('Invalid Permissions', 'Bad Permissions - A');
	}


break;
default:
	send_error('Invalid Request Type','');
}


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