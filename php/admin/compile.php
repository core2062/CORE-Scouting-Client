<?php
$db->compiledTeam->remove([]);//clear out compiledTeam

require_once "php/math/Matrix.php";

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

$teams = [3061, 1625, 2957, 4238, 3754, 3312, 4181, 2470, 3840, 2472, 3261, 3263, 3883, 3056, 3036, 2512, 4009, 4230, 2220, 1816, 3828, 3054, 2499, 2518, 2977, 3755, 3747, 2526, 2177, 2500, 2538, 4217, 3102, 2052, 3276, 3122, 3788, 2845, 3294, 2264, 2169, 2530, 2846, 2574, 3018, 3740, 3267, 2491, 3846, 3839, 3367, 4228, 2175, 3130, 877, 876, 93, 3197, 2506, 4011, 4054, 1714, 2826, 3381, 2062];
$blackList = [4230];//make something to work with this
sort($teams);
/*     ((:?[0-9])?(:?[0-9])?(:?[0-9])?(:?[0-9])?)</a>(:?(?!41vwsY18B13D)(:?.|\n))*41vwsY18B13D">     */


//OPR Calculations
function calcOPR(){
	global $db;
	global $teams;

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
	unset($oprValues);
/*
	usort($opr, function (array $a, array $b){ return $a[1] - $b[1]; });
	
	$oprText = "";
	foreach ($opr as $key => $value) {
		$oprText .= '\n' . $value[0] . '=' . $value[1] . "\n"; 
	}
	fb($oprText);
*/
	//fb($opr);

	return $opr;
}

$opr = calcOPR();

/*
usort($opr, function (array $a, array $b){ return $a[1] - $b[1]; });

$oprText = "";
foreach ($opr as $key => $value) {
	$oprText .= '\n' . $value[0] . '=' . $value[1] . "\n"; 
}
fb($oprText);
*/

//stats

$cursor = $db->sourceTeamInfo->find();

foreach($cursor as $obj){

	//edit $obj here & add analysis stuff

	$obj['opr'] = $opr[$obj["_id"]];//add opr data to team object

	unset($obj['events']);//temporary

	if(in_array($obj["_id"], $teams)){
		$db->compiledTeam->insert($obj);
	}
}

$cursor = $db->sourceScouting->find(['inputType' => 'tracking']);
$counter = 0;
foreach($cursor as $obj){
	$counter++;
	foreach ($obj as $key => $value) {
	if(is_numeric($value)) $obj[$key] = $obj[$key] + 0;//change type of vars if they are actually numbers
}


	//write new data to team object
		$db->sourceScouting->remove(
			[
				'_id' => $obj['_id']
			]
		);//remove old one
		$db->sourceScouting->insert($obj);//insert new one
}
fb($counter);

//process tracking info
$cursor = $db->sourceScouting->find(['inputType' => 'tracking']);

foreach($cursor as $obj){
	if($obj['meta']['use']){

		//get team object (to add data from previous matches together)
		$currentTeam = $db->compiledTeam->findOne(
			[
				'_id' => (int)$obj['teamNum']
			]
		);

		//get comments
		$obj['comments'] = split("\n", $obj['comments']);

		$currentTeam['matches'][$obj['matchNum']] = [
			'comments' => $obj['comments']
		];

		$currentTeam['totalMatches']++;

		//process tracking inputs
		$len = count($obj['trackingInputs']);
		for ($i=0; $i < $len; $i++) {
			$currentObj = $obj['trackingInputs'][$i];//only need this stuff

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


			$currentTeam['shooting']['totalShots']++;
			$currentTeam['matches'][ $obj['matchNum'] ]['shooting']['totalShots']++;

			//add distance info to object
			$currentTeam['matches'][ $obj['matchNum'] ]['shooting']['shots'][] = [
				'result' => empty($currentObj['score']) ? 'missed' : $currentObj['score'],
				'distance' => $distanceFromHoop,
				'place' => $location
			];

			//add to shoot totals
			if(!empty($currentObj['score'])){
				$currentTeam['shooting']['totalScores']++;
				$currentTeam['matches'][ $obj['matchNum'] ]['shooting']['totalScores']++;

				//increase score total for the correct hoop
				$currentTeam['shooting']['heightTotal'][ $currentObj['score'] ]++;
				$currentTeam['matches'][ $obj['matchNum'] ]['shooting']['heightTotal'][ $currentObj['score'] ]++;
			}

			//more stuff here
		}

		//get averages
/*
		foreach($currentTeam['matches'] as $key => $value) {
			$currentTeam['matches'][$key]['shooting'][''];
		}//NOT FINISHED HERE!!!!!!!!!!!!!!!!!
*/


		//check for incorrect teamNum (not fatal if exists, just wrong)
		if(!in_array($obj["teamNum"], $teams)){
			logger('wrong teamNum in match ' . $obj["matchNum"] . ' : ' . $obj["teamNum"], true);
		}

		//write new data to team object
		$db->compiledTeam->remove(
			[
				'_id' => (int)$obj['teamNum']
			]
		);//remove old one
		$db->compiledTeam->insert($currentTeam);//insert new one

		
	}
}

?>