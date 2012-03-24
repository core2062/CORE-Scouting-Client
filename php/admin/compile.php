<?php
require "php/analysis.php";

//$opr = calcOPR(); fuck opr ... port to c++ & don't run w/ data from twitter (use firstDB)

$db->compiledTeam->remove([]);//clear out compiledTeam

$len = count($teams);
for($i=0; $i < $len; $i++){ 
	$obj = $db->sourceTeamInfo->findOne(['_id' => $teams[$i]]);//start with team info

	unset($obj['meta']);
	unset($obj['events']);//temporary

	//$obj['opr'] = $opr[$teams[$i]];//add opr data to team object

	//get scouting info
	$cursor = $db->analysisScouting->find(['teamNum' => $teams[$i]]);
	foreach($cursor as $currentMatch){
		unset($currentMatch['_id']);//just a random id
		unset($currentMatch['teamNum']);

		//merge paper & electronic data here
		if(!empty($obj['matches'][$currentMatch['matchType'] . $currentMatch['matchNum']])){

		}


		$obj['matches'][$currentMatch['matchType'] . $currentMatch['matchNum'] . ' - ' . $currentMatch['inputType']] = $currentMatch;//add match object
		unset($currentMatch['matchType']);
		unset($obj['matches'][$currentMatch['matchNum']]['matchNum']);//now represented in the key for the match
	}

	//TODO: make paper data merge with tracking

	$obj['totalMatches'] = count($obj['matches']);

	//add in indexes
	$obj['totalShots'] = 0;
	$obj['totalScores'] = 0;
	$obj['heightTotal'] = ['top' => 0, 'middle' => 0, 'bottom' => 0];

	//count total shots for team
	foreach($obj['matches'] as $value){
		if(!empty($value['totalShots'])) $obj['totalShots'] = $value['totalShots'] + $obj['totalShots'];
		if(!empty($value['totalScores'])) $obj['totalScores'] = $value['totalScores'] + $obj['totalScores'];
		if(!empty($value['heightTotal'])) $obj['heightTotal'] = array_add([ $value['heightTotal'], $obj['heightTotal'] ]);
	}

	//other analytics

	$db->compiledTeam->insert($obj);
}
?>