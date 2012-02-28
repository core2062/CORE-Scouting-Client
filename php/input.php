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
	'type' => $input['inputType'],
	'use' => true //default
);

fb($input['data']);
fb($insert);

//for robot & alliance
if($input['inputType'] == 'robot' || $input['inputType'] == 'alliance'){
	if(empty($input['data']['matchNum'])){
		send_error("match number was not correct");
	}

	$insert['_id'] = $input['data']['matchNum'];
}

switch ($input['inputType']) {
case "robot":

	//validation of all data... log invalid data & change errorCount based on it
	//if error count is too high then set use = false
	
	$db->raw->insert(
		array_merge($insert, $input);
	);

	send_reg(array('message' => 'data submitted sucessfully'));

	/* Data Model
	
	_id: random number
	meta: {
		scoutid: scoutid of user
		time: when data was entered
		type: inputType (robot, alliance, or pit)
		use: default as true, if error count is too high then it is false
	}
	sort: {
		matchNum:
	}


	*/

	
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