<?php
/*
	NOTICE!!!!!!!!!!!!!!!!!!!
	this script is REALLY old and will do absolutly nothing because it was never updated to mongo or even moved to the older XML based client to server communication that was used before i switched to JSON... it is only here because it contains the structure of the mail system i was making (and might actually finish one day)

	compleating this is very low priority 
*/


//get more parameters
$MailType=$_POST["t"]; // or this can contain the scoutid being sent to (for individual send)
$Message=$_POST["m"];

// clean parameters
$MailType = mysql_real_escape_string($MailType);
$Message = mysql_real_escape_string($Message);

mysql_query ("INSERT INTO log VALUES ('Mail Sending', NOW(), 'ScoutID: $ScoutID | Mailtype: $MailType | Message: $Message')"); //write to log

$ScoutTeam = "";

switch ($MailType) //switch for assigning "to", "from" and "deathat"
{
case 1: //mail to scout leader

$ToID = mysql_query ("SELECT ScoutID FROM users WHERE ScoutTeam='$ScoutTeam' AND(Permission=2 OR Permission=8)");

	break;
case 2: //mail to full team

$ToID = mysql_query ("SELECT ScoutID FROM users WHERE ScoutTeam='$ScoutTeam'");

	break;
case 3: //mail to admin

$ToID = mysql_query ("SELECT ScoutID FROM users WHERE ScoutTeam='$ScoutTeam' AND Permission=9");

	break;
default:  //mail to individual (sender must be scout leader or admin)
if ($permission == 2 || $permission > 7) {
$ToID[0] = $MailType;
}
else {
	die ('-error-Invalid Permissions');
}
};


//send mail to email
//loop through "ScoutID" array (dependant on legnth) to get the email address from the user table ---- (if email address == blank exclude from array)
//loop throught "email" array (just created) and send the mail



if ($MailType == 4) {
	die('1'); //mail sucessfully sent to admin
}


//send message to mail table
//loop through "ScoutID" array (dependant on legnth) to put messages for all scout id's in array


//delete old mail (of anytype -- to prevent mail flood on signin)
?>