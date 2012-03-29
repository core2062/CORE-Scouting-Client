<?php
/*
	this handles all analysis done on single database entries
	this does not compare multiple entries and all error checking is done within the single entry
	this relies on data from sourceScouting and outputs to analysisScouting
*/

$teams = [4371,167,967,3352,2040,81,4296,4143,2115,1736,2039,2481,868,135,3184,4174,2169,48,93,2202,3197,1716,2194,2506,1091,706,3692,1306,1675,1714,1732,1864,2830,3963,4095,4247,930,269,2826,1259,171,1652,3418,3596,537,3381,2077,2062];
$teams = array_diff($teams, globalVar('blacklist'));
/*     ((:?[0-9])?(:?[0-9])?(:?[0-9])?(:?[0-9])?)</a>(:?(?!41vwsY18B13D)(:?.|\n))*41vwsY18echo">     */



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

function writeErrors($errors, $obj){//declare this function as part of entryAnalysis to allow it to access its vars ????
	if(count($errors) != 0){
		globalVarAppend('analysisScoutingErrors', [
			$obj['matchType'] . $obj['matchNum'] => [
				$obj['teamNum'] => [
					$obj['inputType'] => $errors
				]
			]
		]);
	}
}

function entryAnalysis($obj){
	//$obj is the object holding the input scouting data
	global $db;
	global $teams;

	$errors = [];
	
	if(in_array($obj['teamNum'], globalVar('blacklist'))){//remove blacklisted teams
		$errors[] = 'blacklisted team';
		$obj['meta']['use'] = false;
	} else if(!in_array($obj["teamNum"], $teams)){//check for incorrect teamNum
		$errors[] = 'wrong teamNum';
		$obj['meta']['use'] = false;
	}

	if(!$obj['meta']['use']){
		$errors[] =  'data is not usable';
		writeErrors($errors, $obj);//need to write out errors here before return
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
					$distanceFromHoopX = 300-$currentObj['xCoord'];
				} else {
					$distanceFromHoopX = $currentObj['xCoord'];
				}

				$distanceFromHoop = sqrt($distanceFromHoopX^2 + $distanceFromHoopY^2)/sqrt(300^2 + 75^2);//1 is farthest you can get, 0 is right on top of the hoop (it's a percent)

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
	writeErrors($errors, $obj);
}
?>