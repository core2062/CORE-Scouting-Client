<?php
	$db->sourceEventInfo->remove([]);//clear out sourceTeamInfo

	//get file to find number of teams
	//$contents = file_get_contents("https://my.usfirst.org/myarea/index.lasso?page=searchresults&skip_teams=0&programs=FRC&season_FRC=" . $year . "&reports=teams&results_size=25", false);
	$contents = file_get_contents('eventInfo.php');

	//find out how many teams were returned (to determine # of pages)
	preg_match('/All Areas\s+\((....|...|..|.) found,/', $contents, $matches);
	$totalTeamsFound = $matches[1];

	function processTPID($input){//add each team to DB
		global $db;

		$db->sourceTeamInfo->update(
			[
				"_id" => (int)$input[2]
			],
			[
				'$set' => [
					"meta" => [
						"tpid" => (int)$input[1]
					]
				]
			],
			true
		);
		return '';//is this needed or assumed????? & in below
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
	$cursor = $db->sourceTeamInfo->find()->sort(["_id" => 1]);

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

		$input['awards'] = [];

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
			[
				'_id' => $obj['_id']
			],
			[
				'events' => 1
			]
		);

		$events = iterator_to_array($events);

		if(!empty($events[$obj['_id']]['events'])){
			$events = $events[$obj['_id']]['events'];//really only need this part
		} else {
			$events = [];//if the team has no events entered yet
		}
		
		//TODO: make year be integer

		$events[(int)$input[0]][$input[1]]['awards'] = $input['awards'];

		$db->sourceTeamInfo->update(
			[
				"_id" => $obj['_id']
			],
			[
				'$set' => [
					"events" => $events
				]
			],
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
			[
				"_id" => $obj['_id']
			],
			[
				'$set' => [
					'info' => $team
				]
			],
			true
		);

		//get events & input them
		preg_replace_callback("/<tr > <td >([^<>]*)<\/td> <td >([^<>]*)<\/td> <td >((?:[^<>]*|<br \/>|<(?:\/)?i>)*)<\/td> <\/tr>/", "processEvent", $contents);
	}
?>