<?php

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