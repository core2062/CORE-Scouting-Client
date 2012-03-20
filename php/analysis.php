<?php
require_once "php/math/Matrix.php";

$teams = [3061, 1625, 2957, 4238, 3754, 3312, 4181, 2470, 3840, 2472, 3261, 3263, 3883, 3056, 3036, 2512, 4009, 4230, 2220, 1816, 3828, 3054, 2499, 2518, 2977, 3755, 3747, 2526, 2177, 2500, 2538, 4217, 3102, 2052, 3276, 3122, 3788, 2845, 3294, 2264, 2169, 2530, 2846, 2574, 3018, 3740, 3267, 2491, 3846, 3839, 3367, 4228, 2175, 3130, 877, 876, 93, 3197, 2506, 4011, 4054, 1714, 2826, 3381, 2062];
$blackList = [4230];//make something to work with this
sort($teams);
/*     ((:?[0-9])?(:?[0-9])?(:?[0-9])?(:?[0-9])?)</a>(:?(?!41vwsY18B13D)(:?.|\n))*41vwsY18echo">     */

//OPR Calculations
function calcOPR(){
	global $db;
	global $teams;
	//TODO: php is too slow, use a c++ program for OPR

	//ini_set('max_execution_time', 1200); //this script needs extra time
	//ini_set('memory_limit', '1200M');//the OPR calc can require alot of memory

	$teamMatchups = [];
	$teamScores = [];
	$allianceColors = ['red', 'blue'];

	$cursor = $db->sourceFMS->find();

	foreach($cursor as $obj){
		$obj = $obj['data'];//only part that is needed
		if($obj['eventCode'] == "DMN" && $obj['matchType'] != "P"){//use check with mongo???
			for($e=0; $e < 3; $e++){
				for($allianceColorIndex=0; $allianceColorIndex < 2; $allianceColorIndex++){//toggle current alliance
					//$currentAlliance == 'redTeams' ? $oppositeAlliance = 'blueTeams' : $oppositeAlliance = 'redTeams';
					$currentTeam = $obj[$allianceColors[$allianceColorIndex] . 'Teams'][$e];//just for convince & better naming

					if(empty($teamMatchups[$currentTeam])){//if team has not been added yet
						$teamScores[$currentTeam] = 0;
						$teamMatchups[$currentTeam] = array_fill_keys(array_keys($teamMatchups),0);
						foreach ($teamMatchups as $key => $value){
							$teamMatchups[$key][$currentTeam] = 0;
						}
					}
					$teamMatchups[$currentTeam][$currentTeam]++;//increse team vs itself number (number of times it has played)
					$teamScores[$currentTeam] = $teamScores[$currentTeam] + $obj[$allianceColors[$allianceColorIndex] . 'FinalScore'];//increase team total score
				}
			}

			for($e=0; $e < 3; $e++){//seperate so all teams are already defined
				for($i=0; $i < 3; $i++){
					$teamMatchups[ $obj['blueTeams'][$e] ][ $obj['redTeams'][$i] ]++;
					$teamMatchups[ $obj['redTeams'][$e] ][ $obj['blueTeams'][$i] ]++;
				}
			}
		}
	}

	//recursivly sort matrix
	ksort($teamMatchups);
	foreach($teamMatchups as $key => $value){
		ksort($teamMatchups[$key]);
	}

	ksort($teamScores);
	$teamList = array_keys($teamScores);
	$teamScores = array_values($teamScores);

	//remove team numbers from array - turn into normal matrix to it can be put through equation
	$teamMatchups = array_values($teamMatchups);
	foreach($teamMatchups as $key => $value){
		$teamMatchups[$key] = array_values($teamMatchups[$key]);
	}

	$teamMatchupsMatrix = new Math_Matrix($teamMatchups);
	$teamScoresVector = new Math_Vector($teamScores);

	$oprValues = Math_Matrix::solve($teamMatchupsMatrix, $teamScoresVector);
	$oprValues = $oprValues->_tuple->data;

	$len = count($oprValues);
	for($i = 0; $i < $len; $i++){
		//$opr[] = [$teamList[$i], $oprValues[$i]];
		$opr[$teamList[$i]] = $oprValues[$i];
	}

	return $opr;
}

function isIn($objectPoint, $containerPoint1, $containerPoint2){
	//checks if object (a specific point) is in square container
	if($containerPoint1[0] > $containerPoint2[0]){
		//switch points to put highest in point 2
		$tmp = $containerPoint1[0];
		$containerPoint1[0] = $containerPoint2[0];
		$containerPoint2[0] = $tmp;
	}
	if($containerPoint1[1] > $containerPoint2[1]){
		//switch points to put highest in point 2
		$tmp = $containerPoint1[1];
		$containerPoint1[1] = $containerPoint2[1];
		$containerPoint2[1] = $tmp;
	}

	if($objectPoint[0] > $containerPoint2[0] || $objectPoint[0] < $containerPoint1[0] || $objectPoint[1] > $containerPoint2[1] || $objectPoint[1] < $containerPoint1[1]){
		return false;
	} else {
		return true;
	}
}

function analysisScouting(){
	//only call this function if compiledScouting needs to be rebuilt completely
	global $db;

	$db->analysisScouting->remove([]);//clear out analysisScouting

	$cursor = $db->sourceScouting->find(['inputType' => 'tracking']);//process tracking info
	foreach($cursor as $obj){
		trackingEntryAnalysis($obj);
	}
/*
	$cursor = $db->sourceScouting->find(['inputType' => 'robot']);//process robot info
	foreach($cursor as $obj){
		robotEntryAnalysis($obj);
	}
*/
}

function trackingEntryAnalysis($obj){
	//$obj is the object holding the input scouting data
	global $db;
	global $teams;//remove this dependency later

	if(!$obj['meta']['use']) return;//if use is false
	unset($obj['meta']);

	//get comments
	$obj['comments'] = split("\n", $obj['comments']);

	//process shots
	$len = count($obj['shots']);
	for ($i=0; $i < $len; $i++) {
		$currentObj = $obj['shots'][$i];//only need this stuff

		//get shoot locations
		$currentCoords = [ $currentObj['xCoord'] , $currentObj['yCoord'] ];

		//check if it is from key
		if(isIn($currentCoords, [0,53], [89,100]) || isIn($currentCoords, [213,53], [300,100])){
			$location = 'key';
		} else if(isIn($currentCoords, [0,150], [150,129]) || isIn($currentCoords, [300,0], [150,24])){
			$location = 'fender';
		} else {
			$location = 'unspecified';
		}
		
		if(isIn($currentCoords, [0,0], [150,150])){
			$side = 'b';//based on color of key
		} else {
			$side = 'r';
		}

		if(($side == 'b' && $obj['allianceColor'] == 'r') || ($side == 'r' && $obj['allianceColor'] == 'b')){
			if($location == 'fender'){
				$side = 'same';
			} else {
				$side = 'opposite';
			}
		} else {
			if($location == 'fender'){
				$side = 'opposite';
			} else {
				$side = 'same';
			}
		}

		$location = $side . ' Side, ' . $location;

		$distanceFromHoopY = abs($currentObj['yCoord']-75);
		if($obj['allianceColor'] == 'r'){
			$distanceFromHoopX = $currentObj['xCoord'];
		} else {
			$distanceFromHoopX = 300-$currentObj['xCoord'];
		}

		$distanceFromHoop = sqrt($distanceFromHoopX^2 + $distanceFromHoopY^2)/sqrt(300^2 + 75^2);//0 is farthest you can get, 1 is right on top of the hoop (it's a percent)

		//add distance info to object
		$obj['shots'][$i] = [
			'result' => empty($currentObj['score']) ? 'missed' : $currentObj['score'],
			'distance' => $distanceFromHoop,
			'place' => $location,
			'period' => $currentObj['period']
		];
		//more stuff here
	}

	//count total shots/scores
	$obj['totalShots'] = count($obj['shots']);

	//add in objects to prevent undefined index error
	$obj['totalScores'] = 0;
	$obj['heightTotal'] = ['top' => 0, 'middle' => 0, 'bottom' => 0];

	for($matchNum=0; $matchNum < $obj['totalShots']; $matchNum++){ 
		if($obj['shots'][$matchNum]['result'] != 'missed'){
			$obj['totalScores']++;
			$obj['heightTotal'][ $obj['shots'][$matchNum]['result'] ]++;
		};
	}

	//check for incorrect teamNum (not fatal if exists, just wrong)
	if(!in_array($obj["teamNum"], $teams)){
		logger('wrong teamNum in match ' . $obj["matchNum"] . ' : ' . $obj["teamNum"], true);
	}

	//write new data to analysisScouting
	$db->analysisScouting->insert($obj);//insert new one
}

function robotEntryAnalysis($obj){
	//$obj is the object holding the input scouting data
	global $db;

	if(!$obj['meta']['use']) return;//if use is false
	unset($obj['meta']);

	//get comments
	$obj['comments'] = split("\n", $obj['comments']);

	//... analysis

	//check for incorrect teamNum (not fatal if exists, just wrong)
	if(!in_array($obj["teamNum"], $teams)){
		logger('wrong teamNum in match ' . $obj["matchNum"] . ' : ' . $obj["teamNum"], true);
	}

	//write new data to analysisScouting
	$db->analysisScouting->insert($currentTeam);//insert new one
}
?>