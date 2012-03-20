<?php
/*
Requires:
	inputType - robot, tracking, pit...
*/

if(empty($input['inputType']) == true){
	send_error('inputType was not sent');
}

$input['data']['inputType'] = $input['inputType'];

//for all types of input
$input['data']['meta'] = array(
	'scoutid' => $user['_id'],
	'time' => $starttime,
	'type' => $input['inputType'],
	'use' => true //default
);

fb($input['data']);

//for robot & tracking
if($input['inputType'] == 'robot' || $input['inputType'] == 'tracking'){
	if(empty($input['data']['matchNum'])){
		send_error("match number was not correct");
	}
	if(empty($input['data']['teamNum'])){
		send_error("team number was not correct");
	}
}

foreach ($input['data'] as $key => $value) {
	if(is_numeric($value)) $input['data'][$key] = $input['data'][$key] + 0;//change type of vars if they are actually numbers
}

//validation of all data... log invalid data & change errorCount based on it
//if error count is too high then set use = false

$db->sourceScouting->insert(
	$input['data']
);

require "php/analysis.php";
trackingEntryAnalysis($input['data']);

send_reg(array('message' => 'data submitted sucessfully'));
?>