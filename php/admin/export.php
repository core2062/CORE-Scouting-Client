<?php
	//compiled team objects
/*
	$cursor = $db->compiledTeam->find();
	foreach ($cursor as $obj) {
		$obj['teamNum'] = $obj['_id'];
		unset($obj['_id']);

		unset($obj['matches']);
		
		unset($obj['info']['name']);
		unset($obj['info']['site']);
		unset($obj['info']['location']);
		unset($obj['info']['motto']);

		$obj['comments'] = join($obj['comments']," | ");
		$obj['comments'] = preg_replace('/,/', ';', $obj['comments']);

		$csv[] = toFlatArray($obj);
	}
*/
/*
		total number of balances

		next level of importance:

		# matches doa
		penalty points incurred
		total bump crossings
		hybrid score separated from teleop score

		nice to have

		longest shot made (could be represented in feet or in increments like fender, key, front court, half court...)
		best balance (single, double, etc)
*/
/*
	$fp = fopen('tmp/db/export/compiledTeam.csv', "w+");
	fwrite($fp, toCSV($csv));
	fclose($fp);

	unset($csv);
*/
	//robot scouting sheets
	$cursor = $db->analysisScouting->find(['inputType' => 'robot']);
	foreach ($cursor as $obj) {
		unset($obj['meta']);
		unset($obj['_id']);
		unset($obj['inputType']);

		ksort($obj);

		$obj['comments'] = join($obj['comments']," | ");
		$obj['comments'] = preg_replace('/,/', ';', $obj['comments']);

		$csv[] = toFlatArray($obj);
	}

	$fp = fopen('tmp/db/export/sourceScoutingRobot.csv', "w+");
	fwrite($fp, toCSV($csv));
	fclose($fp);

	unset($csv);

	//robot scouting sheets
	$cursor = $db->analysisScouting->find(['inputType' => 'tracking']);
	foreach($cursor as $obj) {
		unset($obj['meta']);
		unset($obj['_id']);
		unset($obj['inputType']);
		unset($obj['shots']);

		ksort($obj);

		$obj['comments'] = join($obj['comments']," | ");
		$obj['comments'] = preg_replace('/,/', ';', $obj['comments']);

		$csv[] = toFlatArray($obj);
	}

	$fp = fopen('tmp/db/export/sourceScoutingTracking.csv', "w+");
	fwrite($fp, toCSV($csv));
	fclose($fp);

	//functions
	function toCSV($csv){//all rows must have same keys
		array_unshift($csv, array_keys($csv[0]));//prepend
		$len = count($csv);
		for($i=0; $i < $len; $i++){ 
			$csv[$i] = join(',', array_values($csv[$i]));
		}
		return join("\n", $csv);
	}

	function toFlatArray($json, $rootKey = ""){
		foreach ($json as $key => $value) {
			if(is_array($value)){
				$newEntries = toFlatArray($value, $rootKey . $key . '.');
				foreach ($newEntries as $newKey => $newValue) {
					$flatArray[$newKey] = $newValue;
				}
			} else {
				$flatArray[$rootKey . $key] = $value;
			}
		}
		return $flatArray;
	}
?>