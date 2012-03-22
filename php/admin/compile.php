<?php
require "php/analysis.php";

$teams = [4371,167,967,3352,2040,81,4296,4143,2115,1736,2039,2481,868,135,3184,4174,2169,48,93,2202,3197,1716,2194,2506,1091];
$blackList = [4230];//make something to work with this
sort($teams);
/*     ((:?[0-9])?(:?[0-9])?(:?[0-9])?(:?[0-9])?)</a>(:?(?!41vwsY18B13D)(:?.|\n))*41vwsY18echo">     */

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