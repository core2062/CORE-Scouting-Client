<?php
/*
Requires:
	inputType - robot, human, pit...
*/

if(empty($input['inputType']) == true){
	send_error('inputType was not sent');
}

//for all types of input
$insert['meta'] = array(
	'scoutid' => $user['_id'],
	'time' => $starttime,
	'type' => $input['inputType']
);

//for robot & human
if($input['inputType'] == 'robot' || $input['inputType'] == 'human'){
	$insert['match']
}

switch ($input['inputType']) {
case "robot":
	
	//validation of all data... log invalid data & change errorCount based on it
	//if error count is too high then set use = false
	
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