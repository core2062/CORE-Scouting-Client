<?php
/*
Requires:
	inputType - robot, tracking, pit...
*/

if(!empty($input['inputType'])){
	$input['data']['inputType'] = $input['inputType'];
} else {
	$input['data']['inputType'] = 'unknown';
}

//for all types of input
$input['data']['meta'] = array(
	'scoutid' => $user['_id'],
	'time' => $starttime,
	'type' => $input['inputType'],
	'eventCode' => 'WI',
	'use' => true //default
);

foreach ($input['data'] as $key => $value) {
	if(is_numeric($value)) $input['data'][$key] = $input['data'][$key] + 0;//change type of vars if they are actually numbers
}

$db->sourceScouting->insert(
	$input['data']
);

logger($input['data']['inputType'] . ' data from match ' . $input['data']['matchNum'] . ' was submitted by ' . $input['data']['meta']['scoutid']);

require "php/scouting/analysis.php";
entryAnalysis($input['data']);

send_reg(array('message' => 'data submitted sucessfully'));
?>