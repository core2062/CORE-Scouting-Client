<?php
/*
Requires:
	inputType - robot, human, pit...
*/

switch ($input['request']) {
case "robot":
	
	//validation of all data... log invalid data & change errorCount based on it
	//if error count is too high then set use = false
	
	$insert = "{
		scoutid:'token-gen',
		time:'$starttime',
		
	}";
	
break;
case "human":
	
	//validation of all data... log invalid data & change errorCount based on it
	//if error count is too high then set use = false
	
break;
case "pit":
	
	//validation of all data... log invalid data & change errorCount based on it
	//if error count is too high then set use = false
	
break;
default:
	send_error('invalid inputType','');
}

$db->execute("db.raw.insert($insert)");

?>