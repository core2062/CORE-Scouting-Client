<?php
/*
Requires:
	subRequest - getTeams, getTeamProfiles...
*/

//TODO: make function to limit history (number of years to track teams back to)

$vars['devMode'] = globalVar('devMode');

if($input['subRequest'] == 'getTeams' || $input['subRequest'] == 'getTeamProfiles'){
	function getSessionID(){
		//TODO: make this more robust/low level & faster

		global $sessionID;
		global $year;

		$contents = file_get_contents("https://my.usfirst.org/myarea/index.lasso?page=searchresults&skip_teams=0&programs=FRC&season_FRC=" . $year . "&reports=teams&results_size=25", false);
		preg_match("/<form action=\"index.lasso\?-session=myarea:([A-Za-z_0-9]*)\" method=\"post\">/", $contents, $matches);
		$sessionID = $matches[1];
		logger("got/updated session key");
	}

	//TODO: make year var more semantic
	$year = 2012;//year to get data from
	getSessionID();
}


switch ($input['subRequest']) {
case "getTeams": //gets the number & tpid (used by FIRST to identify teams) for each team, then gets all profiles

	require_once 'php/admin/getTeams';
	send_reg(['message' => 'finished getting team info']);

break;
case "getEvents": //get all events & add links for teams in each match (which will hold scouting data)

break;
case "updateFMS": //update scores/schedule of current or recent events (uses twitter)
	$db->sourceFMS->remove([]);//clear out sourceFMS

	$since_id = globalVar('since_id');
	$since_id = 132931435354005504;

	$fmsFiltered = [];

	for($i = 1; $i < 17; $i++){
		$filename = "tmp/db/updateFMS/" . $i;

		logger('getting page ' . $i);

		logger(file_exists($filename));
		logger($vars['devMode']);
		logger($since_id);

		if(file_exists($filename) && $vars['devMode']){//check if page is in cache (caching is disabled if not in dev mode)
			logger('getting from cache');

			$fmsFeed = file_get_contents($filename);
		} else {
			logger('getting from twitter');

			$fmsFeed = file_get_contents('http://twitter.com/statuses/user_timeline/frcfms.json?trim_user=true&count=200&since_id=' . $since_id . "&page=" . $i, false);

			logger($fmsFeed);

			if($vars['devMode']){
				$fp = fopen($filename, "w+");
				fwrite($fp, $fmsFeed);
				fclose($fp);
			}
		}

		if($fmsFeed == '[]'){
			logger('reached end of loop (page was blank)');
			break;
		}

		$fmsFeed = json_decode($fmsFeed, true);

		foreach($fmsFeed as $obj){//$i = 0; $i > $len; $i++

			preg_match('/#FRC([^\s]*)/', $obj['text'], $info['eventCode']);
			preg_match('/TY (P|Q|E)/', $obj['text'], $info['matchType']);
			preg_match('/MC ([0-9]*)/', $obj['text'], $info['matchNumber']);

			preg_match('/RF ([0-9]*)/', $obj['text'], $info['redFinalScore']);
			preg_match('/BF ([0-9]*)/', $obj['text'], $info['blueFinalScore']);

			preg_match('/RA ([0-9]*) ([0-9]*) ([0-9]*)/', $obj['text'], $info['redTeams']);
			$info['redTeams'][1] = [(int)$info['redTeams'][1], (int)$info['redTeams'][2], (int)$info['redTeams'][3]];

			preg_match('/BA ([0-9]*) ([0-9]*) ([0-9]*)/', $obj['text'], $info['blueTeams']);
			$info['blueTeams'][1] = [(int)$info['blueTeams'][1], (int)$info['blueTeams'][2], (int)$info['blueTeams'][3]];

			preg_match('/RB ([0-9]*)/', $obj['text'], $info['redBridgePoints']);
			preg_match('/BB ([0-9]*)/', $obj['text'], $info['blueBridgePoints']);

			preg_match('/RFP ([0-9]*)/', $obj['text'], $info['redFoulPoints']);
			preg_match('/BFP ([0-9]*)/', $obj['text'], $info['blueFoulPoints']);

			preg_match('/RHS ([0-9]*)/', $obj['text'], $info['redHybridBasketPoints']);
			preg_match('/BHS ([0-9]*)/', $obj['text'], $info['blueHybridBasketPoints']);

			preg_match('/RTS ([0-9]*)/', $obj['text'], $info['redTeleopBasketPoints']);
			preg_match('/BTS ([0-9]*)/', $obj['text'], $info['blueTeleopBasketPoints']);

			preg_match('/CS (0|1|2)/', $obj['text'], $info['coopertitionPoints']);

			foreach ($info as $key => $value) {
				if(!empty($info[$key][1])){//fixes issue with undefined index... happens becasuse one of the above wasn't found
					$info[$key] = $info[$key][1];
				} else {
					$info[$key] = '';
				}
			}

			$db->sourceFMS->update(
				[
					"_id" => $obj['id_str']
				],
				[
					'$set' => [
						'text' => $obj['text'],
						'data' => $info
					]
				],
				true
			);

			if($obj['id'] > $since_id){
				$new_since_id = $obj['id_str'];
			}
		}
	}

	$since_id = 1;//remove later

	globalVar('since_id', $new_since_id);

	send_reg(['message' => 'finished updating FMS']);
break;
case "compile": //clear out log collection in mongoDB
	
	require_once "php/admin/compile.php";
	send_reg(['message' => 'db is compiled']);

break;
case "export":

	$cursor = $db->compiledTeam->find();
	foreach ($cursor as $obj) {
		$csv[] = [
			'teamNum' => $obj['_id'],
			'matches' => 0,
			'shots' => 0,
			'scores' => 0,
			'topBasketScores' => 0,
			'middleBasketScores' => 0,
			'bottomBasketScores' => 0,
			'balenceAttempts' => 0,
			//number of times bridge was brought down (just indicates ability to bring bridge down, not necessarily balancing)
			'sucessfulBalence' => 0,//total of below 3 
			'1RobotBalence' => 0,
			'2RobotBalence' => 0,
			'3RobotBalence' => 0,
			'coopertitionBalance' => 0
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

	$fp = fopen($filename, "w+");
	fwrite($fp, $contents);
	fclose($fp);

	send_reg(['message' => 'export is avaliable in /tmp/db/exports']);

break;
case "clearTmp": //clear out & rebuild tmp
	
	clearTmp();
	send_reg(['message' => 'tmp directory is rebuilt']);

break;
case "clearLog": //clear out log collection in mongoDB
	
	$db->log->remove([]);
	send_reg(['message' => 'db log is cleared (except for this message)']);

break;
case "resetDB": //make all the collections / vars needed for the site and remove current

	send_error('bad idea, and not finished');
	//TODO: finish & add stuff to empty db (and maybe export into a backup in temp)
	require_once "php/admin/resetDB.php";

break;
case "backupDB": //copy DB to file in tmp/backup

	//TODO: finish
	send_error('not finished');

break;
default:
	send_error('invalid subRequest');
}

//functions

function clearTmp(){
	/* if not working:
	sudo chmod -R 775 /var/www/
	sudo chown -R sean:www-data /var/www/
	*/

	//TODO: fix file permissions below

	$cwd = getcwd();
	system("rm -rf " . $cwd . "/tmp");
	mkdir($cwd . "/tmp/pages", 0777, true);
	mkdir($cwd . "/tmp/backup");
	mkdir($cwd . "/tmp/db/getTPIDs", 0777, true);
	mkdir($cwd . "/tmp/db/getTeamProfiles");
	mkdir($cwd . "/tmp/db/getEvents");
	mkdir($cwd . "/tmp/db/updateFMS");
	mkdir($cwd . "/tmp/db/export");
}
?>