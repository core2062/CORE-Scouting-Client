<?php
	//compiled team objects
	$cursor = $db->compiledTeam->find();
	foreach ($cursor as $obj) {
		$csv[] = [
			'teamNum' => $obj['_id'],
			'opr' => empty($obj['opr']) ? 'null' : $obj['opr'],
			'matches' => empty($obj['totalMatches']) ? 0 : $obj['totalMatches'],
			'shots' => empty($obj['shooting']['totalShots']) ? 0 : $obj['shooting']['totalShots'],
			'scores' => empty($obj['shooting']['totalScores']) ? 0 : $obj['shooting']['totalScores'],
			'topBasketScores' => empty($obj['shooting']['heightTotal']['top']) ? 0 : $obj['shooting']['heightTotal']['top'],
			'middleBasketScores' => empty($obj['shooting']['heightTotal']['middle']) ? 0 : $obj['shooting']['heightTotal']['middle'],
			'bottomBasketScores' => empty($obj['shooting']['heightTotal']['bottom']) ? 0 : $obj['shooting']['heightTotal']['bottom']//,
			//'balenceAttempts' => $obj,
			//number of times bridge was brought down (just indicates ability to bring bridge down, not necessarily balancing)
			//'sucessfulBalence' => $obj,//total of below 3 
			//'1RobotBalence' => $obj,
			//'2RobotBalence' => $obj,
			//'3RobotBalence' => $obj,
			//'coopertitionBalance' => $obj
		];
	}

	/*
		total number of balances

		next level of importance:

		# matches doa
		penalty points incurred
		total bump crossings
1		hybrid score separated from teleop score

		nice to have

		longest shot made (could be represented in feet or in increments like fender, key, front court, half court...)
		best balance (single, double, etc)
	*/

	$fp = fopen('tmp/db/export/compiledTeam.csv', "w+");
	fwrite($fp, toCSV($csv));
	fclose($fp);

	unset($csv);

	//robot scouting sheets
	$cursor = $db->analysisScouting->find(['inputType' => 'robot']);
	foreach ($cursor as $obj) {
		unset($obj['meta']);
		unset($obj['_id']);
		unset($obj['inputType']);

		$obj['comments'] = join($obj['comments']," | ");
		$obj['comments'] = preg_replace('/,/', ';', $obj['comments']);

		$csv[] = $obj;
	}

	$fp = fopen('tmp/db/export/sourceScoutingRobot.csv', "w+");
	fwrite($fp, toCSV($csv));
	fclose($fp);

/*
	//tracking scouting sheets
	$cursor = $db->sourceScouting->find(['inputType' => 'tracking']);
	foreach ($cursor as $obj) {
		$csv[] = [
			'teamNum' => $obj['teamNum'],
			'matchNum' => $obj['matchNum'],
			'allianceColor' => $obj['allianceColor'],
			'matchType' => $obj['matchType'],

			'crossesBump' => $obj['crossesBump'],//true or false
			'canPickup' => $obj['canPickup'],//true or false
			'getsBallsFromBridge' => $obj['getsBallsFromBridge'],//true or false
			'usesKinect' => $obj['usesKinect'],//true or false
			//strategy info
			//shooting info?
			'fouls' => $obj['fouls'],
			'technicalFouls' => $obj['technicalFouls'],
			'comments' => $obj['comments']//,
			//balance info?
		];
	}

	$fp = fopen('tmp/db/export/trackingScouting.csv', "w+");
	fwrite($fp, toCSV($csv));
	fclose($fp);
*/

	//functions
	function toCSV($csv){
		array_unshift($csv, array_keys($csv[0]));//prepend
		$len = count($csv);
		for($i=0; $i < $len; $i++){ 
			$csv[$i] = join(',', array_values($csv[$i]));
		}
		return join("\n", $csv);
	}
?>