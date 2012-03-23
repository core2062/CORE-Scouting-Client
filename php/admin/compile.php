<?php
require "php/analysis.php";

$opr = calcOPR();

$db->compiledTeam->remove([]);//clear out compiledTeam

$len = count($teams);
for($i=0; $i < $len; $i++){ 
	$obj = $db->sourceTeamInfo->findOne(['_id' => $teams[$i]]);//start with team info

	unset($obj['events']);//temporary

	$obj['opr'] = $opr[$teams[$i]];//add opr data to team object

	//get scouting info
	$cursor = $db->analysisScouting->find(['teamNum' => $teams[$i]]);
	foreach($cursor as $currentMatch){
		unset($currentMatch['_id']);//just a random id
		unset($currentMatch['teamNum']);
		$obj['matches'][$currentMatch['matchNum']] = $currentMatch;//add match object
		unset($obj['matches'][$currentMatch['matchNum']]['matchNum']);//because it is now represented in the key for the match
	}

	$obj['totalMatches'] = count($obj['matches']);
	$obj['heightTotal'] = ['top' => 0, 'middle' => 0, 'bottom' => 0];

	//count total shots for team
	for($matchNum=0; $matchNum < $obj['totalMatches']; $matchNum++){ 
		$obj['totalShots'] = $obj['matches'][$matchNum]['totalShots'] + $obj['totalShots'];
		$obj['totalScores'] = $obj['matches'][$matchNum]['totalScores'] + $obj['totalScores'];

		$obj['heightTotal'] = array_add([ $obj['matches'][$matchNum]['heightTotal'], $obj['heightTotal'] ]);
	}

	//other analytics

	$db->compiledTeam->insert($obj);
}
?>