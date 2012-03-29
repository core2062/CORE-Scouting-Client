<?php
require "php/analysis/analysis.php";

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

function compileScouting(){
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
}

?>