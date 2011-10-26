<?php

// admin stuff doesn't need to get filtered if it's past the standard login


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