<?php
/*
Requires:
	inputType - robot, alliance, pit...
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

//for robot & alliance
if($input['inputType'] == 'robot' || $input['inputType'] == 'alliance'){
	$insert['match']
}

switch ($input['inputType']) {
case "robot":
	
	//validation of all data... log invalid data & change errorCount based on it
	//if error count is too high then set use = false
	
break;
case "alliance":
	
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