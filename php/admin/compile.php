<?php
require_once "php/analysis.php";

$teams = [3061, 1625, 2957, 4238, 3754, 3312, 4181, 2470, 3840, 2472, 3261, 3263, 3883, 3056, 3036, 2512, 4009, 4230, 2220, 1816, 3828, 3054, 2499, 2518, 2977, 3755, 3747, 2526, 2177, 2500, 2538, 4217, 3102, 2052, 3276, 3122, 3788, 2845, 3294, 2264, 2169, 2530, 2846, 2574, 3018, 3740, 3267, 2491, 3846, 3839, 3367, 4228, 2175, 3130, 877, 876, 93, 3197, 2506, 4011, 4054, 1714, 2826, 3381, 2062];
$blackList = [4230];//make something to work with this
sort($teams);
/*     ((:?[0-9])?(:?[0-9])?(:?[0-9])?(:?[0-9])?)</a>(:?(?!41vwsY18B13D)(:?.|\n))*41vwsY18echo">     */


analysisScouting();//remove this later
die();
$opr = calcOPR();

$db->compiledTeam->remove([]);//clear out compiledTeam

$len = count($teams);
for($i=0; $i < $len; $i++){ 
	$obj = $db->sourceTeamInfo->findOne(['teamNum' => $teams[$i]]);//start with team info
	unset($obj['events']);//temporary

	$obj['opr'] = $opr[$obj["_id"]];//add opr data to team object

	//get scouting info
	$cursor = $db->analysisScouting->find(['teamNum' => $obj["_id"]]);
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