<?php
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
		hybrid score seperated from teleop score

		nice to have

		longest shot made (could be represented in feet or in increments like fender, key, front court, half court...)
		best balance (single, double, etc)
	*/

	function toCSV($csv){
		array_unshift($csv, array_keys($csv[0]));//prepend
		$len = count($csv);
		for($i=0; $i < $len; $i++){ 
			$csv[$i] = join(',', array_values($csv[$i]));
		}
		return join("\n", $csv);
	}

	$fp = fopen('tmp/db/export/teams.csv', "w+");
	fwrite($fp, toCSV($csv));
	fclose($fp);
?>