<?php
/*
Requires:
	subRequest - getTeams, getTeamProfiles...
*/

//TODO: make function to limit history (number of years to track teams back to)

function getSessionID(){
	//TODO: make this more robust/low level & faster

	global $sessionID;
	global $year;

	$contents = file_get_contents("https://my.usfirst.org/myarea/index.lasso?page=searchresults&skip_teams=0&programs=FRC&season_FRC=" . $year . "&reports=teams&results_size=25", false);
	preg_match("/<form action=\"index.lasso\?-session=myarea:([A-Za-z_0-9]*)\" method=\"post\">/", $contents, $matches);
	$sessionID = $matches[1];
	logger("got/updated session key");
}

if($input['subRequest'] == 'getTeams' || $input['subRequest'] == 'getTeamProfiles'){
	//TODO: make year var more semantic
	$year = 2012;//year to get data from
	getSessionID();
}


switch ($input['subRequest']) {
case "getTeams": //gets the number & tpid (used by FIRST to identify teams) for each team in that year's competition

	$db->team->remove(array());//clear out database

	//get file to find number of teams
	$contents = file_get_contents("https://my.usfirst.org/myarea/index.lasso?page=searchresults&skip_teams=0&programs=FRC&season_FRC=" . $year . "&reports=teams&results_size=25", false);

	//find out how many teams were returned (to determine # of pages)
	preg_match('/All Areas\s+\((....|...|..|.) found,/', $contents, $matches);
	$totalTeamsFound = $matches[1];

	function processTeamEntry($input){//add each team to DB
		global $db;

		$db->team->update(
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

	//get pages from the FIRST site 
	for($i = 0; $i < ($totalTeamsFound/250); $i++){
		$filename = "tmp/db/getTeams/" . $year . "-" . $i;
	
		if(file_exists($filename)){//check if page is in cache
			$contents = file_get_contents($filename);
		} else {
			$url = "https://my.usfirst.org/myarea/index.lasso?page=searchresults&results_size=250&omit_searchform=1&skip_teams=" . $i*250 . "&-session=myarea:" . $sessionID . "#FRC_teams";
			$contents = file_get_contents($url, false);//consider replacing with cURL

			//remove crap from files
			//TODO: test if below improves speed

			$contents = preg_replace('/\s+/', ' ',$contents, -1, $replacements); //removes double spaces, indents, and line breaks
			fb($replacements);

			//TODO: find fix and remove the 2 below regex... adding "s" to 2nd regex uses too much memory and = seg fault 
			$contents = preg_replace('/<\/a>((?!<a).)*<a/', "</a><a", $contents, -1, $replacements); //removes other crap... another good one: '/<(\/)?t[^<>]*>/'
			$contents = preg_replace('/&amp;-session=myarea:'. $sessionID . '"/', "", $contents, -1, $replacements);
			//&amp;-session=myarea:C77D640507604078D1OjXUqD64EA"
			fb($replacements);
			$contents = preg_replace('/\s+/', ' ',$contents, -1, $replacements); //removes double spaces, indents, and line breaks
			fb($replacements);

			$fp = fopen($filename, "w+");
			fwrite($fp, $contents);
			fclose($fp);
		}

		//question: is it better to run a regex on several small files of one big one
		$regex = '/<a href="\?page=team_details&tpid=(.....)><b>(....|...|..|.)<\/b><\/a>/';
		preg_replace_callback($regex, "processTeamEntry", $contents);
	}

	send_reg(array('message' => 'finished getting the number and tpid of each team'));
	
break;
case "getTeamProfiles": //get profiles of each team. requires a tpid for each team
	ini_set('max_execution_time', 90); //this script needs extra time ?????
	//TODO: is above needed

	$cursor = $db->team->find()->sort(array("_id" => 1));

	//define the function - actually get used at end of team-get 
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
		$events = $db->team->find(
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
			$events = array();//at this point, the script finds that there was no point in the last ~20 lines
		}
		
		//TODO: make year be integer

		$events[(int)$input[0]][$input[1]]['awards'] = $input['awards'];

		$db->team->update(
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

		if($count > 200){
			getSessionID();
			$count = 0;
		}

		logger('getting team:' . $obj['_id']);

		$url = "https://my.usfirst.org/myarea/index.lasso?page=team_details&tpid=" . $obj['meta']['tpid'] . "&-session=myarea:" . $sessionID;
		$contents = file_get_contents($url, false);//TODO: add way to re-try if connection times out 

		$contents = preg_replace('/(?:(v)?align="[a-z]*"|nowrap|bgcolor="#......"|width="..(?:.)?%"|<!--(.|\s)*?-->)/', '', $contents); //removes comments double spaces, indents, line breaks, and other crap
		$contents = preg_replace('/\s+/', ' ',$contents); //removes 

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
			//TODO: fix issue with undefined index (not deadly) ... think it's happening becasuse one of the above wasn't found
			$team[$key] = $team[$key][1];
		}

		settype($team['rookieYear'], "int");

		$team['name'] = utf8_encode(html_entity_decode($team['name']));
		$team['location'] = utf8_encode(html_entity_decode($team['location']));
		$team['nickname'] = utf8_encode(html_entity_decode($team['nickname']));
		$team['motto'] = utf8_encode(html_entity_decode($team['motto']));

		$db->team->update(
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

		preg_replace_callback("/<tr > <td >([^<>]*)<\/td> <td >([^<>]*)<\/td> <td >((?:[^<>]*|<br \/>|<(?:\/)?i>)*)<\/td> <\/tr>/", "processEvent", $contents);

		$count++;
	}

	send_reg(array('message' => 'finished getting team profiles'));
break;
case "getEvents": //get all events & add links for teams in each match (which will hold scouting data)

break;
case "updateEvents": //update scores/schedule of current or recent events (uses twitter api)

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
	mkdir($cwd . "/tmp/db/getTeams", 0777, true);
	mkdir($cwd . "/tmp/db/getTeamProfiles");
	mkdir($cwd . "/tmp/db/getEvents");
	mkdir($cwd . "/tmp/db/updateEvents");

	send_reg(array('message' => 'tmp directory is rebuilt'));

break;
case "clearLog": //clear out log collection in mongoDB
	
	$db->log->remove(array());
	send_reg(array('message' => 'db log is cleared (except for this message)'));

break;
case "resetDB": //make all the collections / vars needed for the site
	
	//general
	$db->createCollection("user");

	$db->globalVar->insert(
		array(
			"_id"=> "admin",
			"permission"=> 9,
			"token"=> "",

			"info"=> array(
				"fName"=> "Sean",
				"lName"=> "Lang",
				"team"=> 2062
			),

			"prefs"=> array(
				"fade"=> true,
				"verbose"=> true
			),

			"account"=> array(
				"pword"=> "superpass",
				"email"=> "slang800@gmail.com"
			),

			"stats"=> array(
				"ip"=> "",
				"logintime"=> 0
			),

			"opt"=> array(
				"zip"=> 0,
				"browser"=> "Firefox",
				"gender"=> "m"
			)
		)
	);

	$db->createCollection("log");
	$db->createCollection("globalVar");

	$db->globalVar->insert(
		array(
			"_id" => "since_id",//for updateFMS (used while interacting with twitter)
			"value" => 0
		),
		array(
			"_id" => "devMode",//sets global devMode, if true, will override local devMode (in index.php)
			"value" => false
		)
	);


	//compiled collections
	$db->createCollection("compiledEvent");
	$db->createCollection("compiledTeam");


	//source collections
	$db->createCollection("sourceScouting");
	$db->createCollection("sourceFMS");
	$db->createCollection("sourceTeamInfo");
	$db->createCollection("sourceEventInfo");
	
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