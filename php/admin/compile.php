<?php
require "php/analysis.php";

$opr = calcOPR();

$db->compiledTeam->remove([]);//clear out compiledTeam

$len = count($teams);
for($i=0; $i < $len; $i++){ 
	$obj = $db->sourceTeamInfo->findOne(['teamNum' => $teams[$i]]);//start with team info
	unset($obj['events']);//temporary

	$obj['opr'] = $opr[$teams[$i]];//add opr data to team object

	//get scouting info
	$cursor = $db->analysisScouting->find(['teamNum' => $teams[$i]]);
	foreach($cursor as $currentMatch){
		$obj['matches'][$currentMatch['matchNum']] = $currentMatch;//add match object
		unset($obj['matches'][$currentMatch['matchNum']]['matchNum']);//because it is now represented in the key for the match
	}

	$obj['totalMatches'] = count($obj['matches']);

	//count total shots for team
	for($matchNum=0; $matchNum < $obj['totalMatches']; $matchNum++){ 
		$obj['totalShots'] = $obj['matches'][$matchNum]['totalShots'] + $obj['totalShots'];
		$obj['totalScores'] = $obj['matches'][$matchNum]['totalScores'] + $obj['totalScores'];

		$obj['heightTotal']['top'] = $obj['matches'][$matchNum]['heightTotal']['top'] + $obj['heightTotal']['top'];
		$obj['heightTotal']['middle'] = $obj['matches'][$matchNum]['heightTotal']['middle'] + $obj['heightTotal']['middle'];
		$obj['heightTotal']['bottom'] = $obj['matches'][$matchNum]['heightTotal']['bottom'] + $obj['heightTotal']['bottom'];
	}

	//other analytics

	$db->compiledTeam->insert($obj);
}
?>