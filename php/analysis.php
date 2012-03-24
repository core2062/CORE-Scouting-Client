<?php
require_once "php/math/Matrix.php";

$teams = [4371,167,967,3352,2040,81,4296,4143,2115,1736,2039,2481,868,135,3184,4174,2169,48,93,2202,3197,1716,2194,2506,1091,706,3692,1306,1675,1714,1732,1864,2830,3963,4095,4247,930,269,2826,1259,171,1652,3418,3596,537,3381,2077,2062];
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

function mutualErrorCheck($obj){
	global $teams;

}

function analysisScoutingRebuild(){
	//only call this function if compiledScouting needs to be rebuilt completely
	global $db;

	$db->analysisScouting->remove([]);//clear out analysisScouting
	globalVar('analysisScoutingErrors',[]);

	$cursor = $db->sourceScouting->find(array_merge(['inputType' => 'tracking'], globalVar('analysisQueryLimits')));//process tracking info
	foreach($cursor as $obj){
		entryAnalysis($obj);
	}

	//FIXER
	/*
	$cursor = $db->sourceScouting->find(['inputType' => 'robot']);//process robot info
	foreach($cursor as $obj){
		if(empty($obj['totalShots'])){
			$obj['totalShots'] = $obj['shotsTaken'];
			unset($obj['shotsTaken']);
			$db->sourceScouting->update(['_id' => $obj['_id']],$obj);//insert new one
		}
	}
	*/
	$cursor = $db->sourceScouting->find(array_merge(['inputType' => 'robot'], globalVar('analysisQueryLimits')));//process robot info
	foreach($cursor as $obj){
		entryAnalysis($obj);
	}	
}

function entryAnalysis($obj){
	//$obj is the object holding the input scouting data
	global $db;
	global $teams;

	$errors = [];

	//check for incorrect teamNum
	if(!in_array($obj["teamNum"], $teams)){
		$errors[] = 'wrong teamNum in ' . $obj['inputType'] . ' scouting data for teamNum=' . $obj["teamNum"];
		$obj['meta']['use'] = false;
	}

	if(in_array($obj['_id'], globalVar('blacklist'))){//remove teams that are literally not worth my cpu cycles
		$errors[] = 'blacklisted team in ' . $obj['inputType'] . ' scouting data';
		$obj['meta']['use'] = false;
	}

	if(!$obj['meta']['use']){
		$errors[] =  $obj['inputType'] . ' scouting data is not usable for teamNum=' . $obj["teamNum"];
		globalVarAppend('analysisScoutingErrors', [$obj['matchType'] . $obj['matchNum'] => $errors]);//need to write out errors here before return
		return;
	}
	unset($obj['meta']);

	//process comments
	$obj['comments'] = preg_split("/\n/", $obj['comments']);
	$len = count($obj['comments']);
	for($i=0; $i < $len; $i++){ 
		$obj['comments'][$i] = trim($obj['comments'][$i]);
		if(empty($obj['comments'][$i])) unset($obj['comments'][$i]);
	}

	if($obj['inputType'] == 'tracking'){
		//process shots
		if(!empty($obj['shots'])){
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

				$location = $side . ' side ' . $location;

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
		}
		
		//count total shots/scores
		!empty($obj['shots']) ? $obj['totalShots'] = count($obj['shots']) : $obj['totalShots'] = 0;

		//add in objects to prevent undefined index error
		$obj['totalScores'] = 0;
		$obj['heightTotal'] = ['top' => 0, 'middle' => 0, 'bottom' => 0];
		$obj['averageDistance'] = 0;

		for($shotNum=0; $shotNum < $obj['totalShots']; $shotNum++){ 
			if($obj['shots'][$shotNum]['result'] != 'missed'){
				$obj['averageDistance'] = $obj['averageDistance'] + $obj['shots'][$shotNum]['distance'];
				$obj['heightTotal'][ $obj['shots'][$shotNum]['result'] ]++;
			};
		}

		$obj['totalScores'] = array_sum(array_values($obj['heightTotal']));

		//turn $obj['averageDistance'] into average (not total)
		if($obj['totalScores'] != 0){
			$obj['averageDistance'] = $obj['averageDistance'] / $obj['totalScores'];
		}
	} else if($obj['inputType'] == 'robot'){

		//... analysis

		$obj['hybridHeightTotal'] = [
			'top' => $obj['hybridHigh'],
			'middle' => $obj['hybridMiddle'],
			'bottom' => $obj['hybridBottom']
		];
		unset($obj['hybridHigh']);
		unset($obj['hybridMiddle']);
		unset($obj['hybridBottom']);

		$obj['teleopHeightTotal'] = [
			'top' => $obj['teleopHigh'],
			'middle' => $obj['teleopMiddle'],
			'bottom' => $obj['teleopBottom']
		];
		unset($obj['teleopHigh']);
		unset($obj['teleopMiddle']);
		unset($obj['teleopBottom']);

		//add in objects to prevent undefined index error
		$obj['heightTotal'] = array_add([ $obj['hybridHeightTotal'], $obj['teleopHeightTotal'] ]);
		//TODO: change "high" to "top" in robot scouting input

		$obj['totalScores'] = array_sum(array_values($obj['heightTotal']));

		//TODO: add period breakdown
	} else {
		$errors[] = 'incorrect input type';
		$obj['meta']['use'] = false;//TODO: add use check to end of function too
	}

	//write new data to analysisScouting
	$db->analysisScouting->insert($obj);//insert new one

	if(count($errors) != 0){
		globalVarAppend('analysisScoutingErrors', [$obj['matchType'] . $obj['matchNum'] => $errors]);
	}
}
?>