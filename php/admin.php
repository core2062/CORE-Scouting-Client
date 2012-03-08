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

	$db->sourceTeamInfo->remove(array());//clear out sourceTeamInfo

	//get file to find number of teams
	$contents = file_get_contents("https://my.usfirst.org/myarea/index.lasso?page=searchresults&skip_teams=0&programs=FRC&season_FRC=" . $year . "&reports=teams&results_size=25", false);

	//find out how many teams were returned (to determine # of pages)
	preg_match('/All Areas\s+\((....|...|..|.) found,/', $contents, $matches);
	$totalTeamsFound = $matches[1];

	function processTPID($input){//add each team to DB
		global $db;

		$db->sourceTeamInfo->update(
			array(
				"_id" => (int)$input[2]
			),
			array(
				'$set' => array(
					"meta" => array(
						"tpid" => (int)$input[1]
					)
				)
			),
			true
		);
		return "";//is this needed or assumed????? & in below
	}

	//get team list pages from the FIRST site 
	for($i = 0; $i < ($totalTeamsFound/250); $i++){
		$filename = "tmp/db/getTPIDs/" . $year . "-" . $i;
	
		if(file_exists($filename) && $vars['devMode']){//check if page is in cache (caching is disabled if not in dev mode)
			$contents = file_get_contents($filename);
		} else {
			$url = "https://my.usfirst.org/myarea/index.lasso?page=searchresults&results_size=250&omit_searchform=1&skip_teams=" . $i*250 . "&-session=myarea:" . $sessionID . "#FRC_teams";
			$contents = file_get_contents($url, false);//consider replacing with cURL

			//remove crap from files
			//TODO: test if below improves speed
			$contents = preg_replace('/\s+/', ' ',$contents, -1, $replacements); //removes double spaces, indents, and line breaks

			//TODO: find fix and remove the 2 below regex... adding "s" to 2nd regex uses too much memory and = seg fault 
			$contents = preg_replace('/<\/a>((?!<a).)*<a/', "</a><a", $contents, -1, $replacements); //removes other crap... another good one: '/<(\/)?t[^<>]*>/'
			$contents = preg_replace('/&amp;-session=myarea:'. $sessionID . '"/', "", $contents, -1, $replacements);
			//&amp;-session=myarea:C77D640507604078D1OjXUqD64EA"

			$contents = preg_replace('/\s+/', ' ',$contents, -1, $replacements); //removes double spaces, indents, and line breaks


			if($vars['devMode']){
				$fp = fopen($filename, "w+");
				fwrite($fp, $contents);
				fclose($fp);
			}
		}

		//question: is it better to run a regex on several small files of one big one
		$regex = '/<a href="\?page=team_details&tpid=(.....)><b>(....|...|..|.)<\/b><\/a>/';
		preg_replace_callback($regex, "processTPID", $contents);
	}

	logger('finished getting the number and tpid of each team');



	//team profile getter

	ini_set('max_execution_time', 120); //this script needs extra time


	//read TPIDs that were just processed
	$cursor = $db->sourceTeamInfo->find()->sort(array("_id" => 1));

	function processEvent($input){//add each team to DB
		global $db;
		global $obj;

		unset($input[0]);//this isn't needed
		$input = array_values($input);//reset keys in array

		//decode html characters
		$len = count($input);
		for ($i=1; $i < $len; $i++) {// first value is year of competition -- no special characters
			$input[$i] = html_entity_decode($input[$i]);
		}

		$input['awards'] = array();

		//split awards string into array
		for ($i=2; $i < $len; $i++) {// award list starts at 2nd array element
			$input['awards'] = (array)explode("<br />", $input[$i]) + $input['awards'];
			unset($input[$i]);
		}

		//search for blank values in awards array & remove extra whitespace
		$len = count($input['awards']);
		for ($i=0; $i < $len; $i++) {
			$input['awards'][$i] = trim($input['awards'][$i]);
			if(empty($input['awards'][$i]) || $input['awards'][$i] == '<i>(2000 and prior award listings may be incomplete)</i>'){
				unset($input['awards'][$i]);
			}
		}

		$input['awards'] = array_values($input['awards']);//reset keys in array

		$input[1] = str_replace($input[0] . ' ', '', $input[1]);//remove year prefix

		//finally add to DB
		//TODO: find faster way to do this (w/out copying and re-adding)
		$events = $db->sourceTeamInfo->find(
			array(
				'_id' => $obj['_id']
			),
			array(
				'events' => 1
			)
		);

		$events = iterator_to_array($events);

		if(!empty($events[$obj['_id']]['events'])){
			$events = $events[$obj['_id']]['events'];//really only need this part
		} else {
			$events = array();//if the team has no events entered yet
		}
		
		//TODO: make year be integer

		$events[(int)$input[0]][$input[1]]['awards'] = $input['awards'];

		$db->sourceTeamInfo->update(
			array(
				"_id" => $obj['_id']
			),
			array(
				'$set' => array(
					"events" => $events
				)
			),
			true
		);
		return "";
	}

	$count = 0;//after x teams, get new sessionID

	//TODO: switch to seperate processes to built cache of pages to process & then extract data (low priority)
	foreach($cursor as $obj){

		logger('getting team:' . $obj['_id']);

		$filename = "tmp/db/getTeamProfiles/" . $obj['_id'];

		if(file_exists($filename) && $vars['devMode']){//check if page is in cache (caching is disabled if not in dev mode)
			$contents = file_get_contents($filename);
		} else {
			if($count > 200){
				getSessionID();
				$count = 0;
			}
			$count++;

			$url = "https://my.usfirst.org/myarea/index.lasso?page=team_details&tpid=" . $obj['meta']['tpid'] . "&-session=myarea:" . $sessionID;
			//TODO: add way to re-try if connection times out 
			$contents = file_get_contents($url, false);//consider replacing with cURL

			//remove crap from files
			//TODO: test if below improves speed
			$contents = preg_replace('/(?:(v)?align="[a-z]*"|nowrap|bgcolor="#......"|width="..(?:.)?%"|<!--(.|\s)*?-->)/', '', $contents); //removes comments, and other crap
			$contents = preg_replace('/\s+/', ' ',$contents); //removes spaces

			if($vars['devMode']){
				$fp = fopen($filename, "w+");
				fwrite($fp, $contents);
				fclose($fp);
			}
		}

		//get basic team info
		preg_match("/<td >Team Name<\/td> <td>([^<>]*)<\/td>/", $contents, $team['name']);
		preg_match("/<td >Team Location<\/td> <td>([^<>]*)<\/td>/", $contents, $team['location']);
		preg_match("/<td >Rookie Season<\/td> <td>(....)<\/td>/", $contents, $team['rookieYear']);
		preg_match("/<td >Team Nickname<\/td> <td>([^<>]*)<\/td>/", $contents, $team['nickname']);
		preg_match("/<td >Team Motto<\/td> <td>([^<>]*)<\/td>/", $contents, $team['motto']);
		preg_match("/<td >Team Website<\/td> <td><a(?:[^>]*)?>([^<>]*)<\/a><\/td>/", $contents, $team['site']);
		
		//TODO: change to using a single object that gets inserted - low priority

		//all those pregs had 1 backreferance, this moves the matches to proper place in array
		//also decode them
		foreach ($team as $key => $value) {

			if(!empty($team[$key][1])){//fixes issue with undefined index... happens becasuse one of the above wasn't found
				$team[$key] = $team[$key][1];
			} else {
				$team[$key] = '';
			}
			
		}

		settype($team['rookieYear'], "int");

		$team['name'] = utf8_encode(html_entity_decode($team['name']));
		$team['location'] = utf8_encode(html_entity_decode($team['location']));
		$team['nickname'] = utf8_encode(html_entity_decode($team['nickname']));
		$team['motto'] = utf8_encode(html_entity_decode($team['motto']));

		//insert basic data
		$db->sourceTeamInfo->update(
			array(
				"_id" => $obj['_id']
			),
			array(
				'$set' => array(
					'info' => $team
				)
			),
			true
		);

		//get events & input them
		preg_replace_callback("/<tr > <td >([^<>]*)<\/td> <td >([^<>]*)<\/td> <td >((?:[^<>]*|<br \/>|<(?:\/)?i>)*)<\/td> <\/tr>/", "processEvent", $contents);
	}

	send_reg(array('message' => 'finished getting team info'));

break;
case "getEvents": //get all events & add links for teams in each match (which will hold scouting data)

break;
case "updateFMS": //update scores/schedule of current or recent events (uses twitter)
	$db->sourceFMS->remove(array());//clear out sourceFMS

	$since_id = globalVar('since_id');
	$since_id = 132931435354005504;

	$fmsFiltered = array();

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
			$info['redTeams'][1] = array((int)$info['redTeams'][1], (int)$info['redTeams'][2], (int)$info['redTeams'][3]);

			preg_match('/BA ([0-9]*) ([0-9]*) ([0-9]*)/', $obj['text'], $info['blueTeams']);
			$info['blueTeams'][1] = array((int)$info['blueTeams'][1], (int)$info['blueTeams'][2], (int)$info['blueTeams'][3]);

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
				array(
					"_id" => $obj['id_str']
				),
				array(
					'$set' => array(
						'text' => $obj['text'],
						'data' => $info
					)
				),
				true
			);

			if($obj['id'] > $since_id){
				$new_since_id = $obj['id_str'];
			}
		}
	}

	$since_id = 1;//remove later

	globalVar('since_id', $new_since_id);

	send_reg(array('message' => 'finished updating FMS'));
break;
case "compile": //clear out log collection in mongoDB
	$db->compiledTeam->remove(array());//clear out compiledTeam
	
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

	$mnTeams = [3061, 1625, 2957, 4238, 3754, 3312, 4181, 2470, 3840, 2472, 3261, 3263, 3883, 3056, 3036, 2512, 4009, 4230, 2220, 1816, 3828, 3054, 2499, 2518, 2977, 3755, 3747, 2526, 2177, 2500, 2538, 4217, 3102, 2052, 3276, 3122, 3788, 2845, 3294, 2264, 2169, 2530, 2846, 2574, 3018, 3740, 3267, 2491, 3846, 3839, 3367, 4228, 2175, 3130, 877, 876, 93, 3197, 2506, 4011, 4054, 1714, 2826, 3381, 2062];
	/*     ((:?[0-9])?(:?[0-9])?(:?[0-9])?(:?[0-9])?)</a>(:?(?!41vwsY18B13D)(:?.|\n))*41vwsY18B13D">     */



	//OPR Calculations

	$teamMatchups = [];
	$teamScores = [];

	$cursor = $db->sourceFMS->find();

	foreach($cursor as $obj){
		$obj = $obj['data'];//only part that is needed

		for($e=0; $e < 3; $e++){
			$teamMatchups[ $obj['blueTeams'][$e] ][ $obj['blueTeams'][$e] ]++;
			$teamMatchups[ $obj['redTeams'][$e] ][ $obj['redTeams'][$e] ]++;

			$teamScores[ $obj['blueTeams'][$e] ] = $teamScores[ $obj['blueTeams'][$e] ] + $obj['blueFinalScore'];
			$teamScores[ $obj['redTeams'][$e] ] = $teamScores[ $obj['redTeams'][$e] ] + $obj['redFinalScore'];

			for($i=0; $i < 3; $i++){
				$teamMatchups[ $obj['blueTeams'][$e] ][ $obj['redTeams'][$i] ]++;
				$teamMatchups[ $obj['redTeams'][$e] ][ $obj['blueTeams'][$i] ]++;
			}
		}
	}

	//multiply inverse of teamMatchups by teamScores

	//fb($teamMatchups);
	//fb($teamScores);
	
	//die();


	//stats

	$cursor = $db->sourceTeamInfo->find();

	foreach($cursor as $obj){
		if($obj['meta']['use']){//do use check with mongo???
			//edit $obj here & add analysis stuff




			if(in_array($obj["_id"], $mnTeams)){
				$db->compiledTeam->insert($obj);
			}
		}	
	}


	//process tracking info
	$cursor = $db->sourceScouting->find(
		array(
			'inputType' => 'alliance'
		)
	);

	foreach($cursor as $obj){
		if($obj['meta']['use']){
			$len = count($obj['trackingInputs']);
			for ($i=0; $i < $len; $i++) {
				$currentObj = $obj['trackingInputs'][$i];//only need this stuff

				if($currentObj['type'] == 'shoot'){
					$shooting['totalShots']++;

					if(!empty($currentObj['score'])){
						$shooting['totalScores']++;
						$shooting['locationTotal'][ $currentObj['score'] ]++;
					}
				}



			}

			//write new data to team object
		}

		
/*
		if(in_array($obj["_id"], $mnTeams)){
			$db->compiledTeam->insert($obj);
		}
*/
	}

	send_reg(array('message' => 'db is compiled'));

break;
case "clearTmp": //clear out & rebuild tmp
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

	send_reg(array('message' => 'tmp directory is rebuilt'));

break;
case "clearLog": //clear out log collection in mongoDB
	
	$db->log->remove(array());
	send_reg(array('message' => 'db log is cleared (except for this message)'));

break;
case "resetDB": //make all the collections / vars needed for the site and remove current

	//TODO: finish & add stuff to empty db (and maybe export into a backup in temp)
	require_once "php/admin/resetDB.php";

break;
case "backupDB": //copy DB to file in tmp/backup

	//TODO: finish

break;
default:
	send_error('invalid subRequest');
}


/*
Errorcheck
Recalculate
Reset DB
*/
?>