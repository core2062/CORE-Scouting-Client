<?php
/*
Requires:
	subRequest - getTeams, getTeamProfiles...
*/

//get $sessionID
//TODO: make sessionID getter more robust/low level
if($input['subRequest'] == 'getTeams' || $input['subRequest'] == 'getTeamProfiles'){
	$year = 2012;//year to get data from
	$url = "https://my.usfirst.org/myarea/index.lasso?page=searchresults&skip_teams=0&programs=FRC&season_FRC=" . $year . "&reports=teams&results_size=25";
	$contents = file_get_contents($url, false);
	preg_match("/<form action=\"index.lasso\?-session=myarea:([A-Za-z_0-9]*)\" method=\"post\">/", $contents, $matches);
	$sessionID = $matches[1];
	logger("got session key");
}


switch ($input['subRequest']) {
case "getTeams": //gets the number & tpid of each team

	//find out how many teams were returned (to determine # of pages) - use same content as sessionID getter
	preg_match('/All Areas\s+\((....|...|..|.) found,/', $contents, $matches);
	$totalTeamsFound = $matches[1];

	$contents = "";//reuse variable below

	//get a bunch of pages from the FIRST site 
	for($i = 0; $i < ($totalTeamsFound/250); $i++){
		$url = "https://my.usfirst.org/myarea/index.lasso?page=searchresults&results_size=250&omit_searchform=1&skip_teams=" . $i*250 . "&-session=myarea:" . $sessionID . "#FRC_teams";

		$temp = file_get_contents($url, false);

		//test if below improves regex speed
		$temp = preg_replace('/<!--(.|\s)*?-->/', '', $temp); //removes comments
		$temp = preg_replace('/\s+/', ' ',$temp); //removes double spaces, indents, and line breaks

		$contents .= $temp;
	}

	//process the pages using a regex, to get the tpid (used by FIRST to identify teams) for each team in that year's competition
	function processTeamEntry($input){//add each team to DB
		global $db;

		//TODO: fix this update thing!!!!!!!!!!!!!!!

		$db->team->update(
			array(
				"_id" => (int)$input[2]
			),
			array(
				"_id" => (int)$input[2],
				"meta" => array(
					"tpid" => (int)$input[1]
				)
			),
			true
		);
	}

	$regex = '/<a href="\?page=team_details&tpid=(.....)&amp;-session=myarea:'. $sessionID . '"><b>(....|...|..|.)<\/b><\/a>/';
	preg_replace_callback($regex, "processTeamEntry", $contents);

	send_reg(array('message' => 'finished getting the number and tpid of each team'));
	
break;
case "getTeamProfiles": //get profiles of each team. requires a tpid for each team

	$cursor = $db->team->find()->sort(array("_id" => 1));

	foreach($cursor as $obj){
		$url = "https://my.usfirst.org/myarea/index.lasso?page=team_details&tpid=" . $obj['meta']['tpid'] . "&-session=myarea:" . $sessionID;
		$contents = file_get_contents($url, false);

		$contents = preg_replace('/(?:(v)?align="[a-z]*"|nowrap|bgcolor="#......"|width="..(?:.)?%"|<!--(.|\s)*?-->)/', '', $contents); //removes comments double spaces, indents, line breaks, and other crap
		$contents = preg_replace('/\s+/', ' ',$contents); //removes 

		preg_match("/<td >Team Name<\/td> <td>([^<>]*)<\/td>/", $contents, $teamName);
		preg_match("/<td >Team Location<\/td> <td>([^<>]*)<\/td>/", $contents, $teamLocation);
		preg_match("/<td >Rookie Season<\/td> <td>(....)<\/td>/", $contents, $teamRookieYear);
		preg_match("/<td >Team Nickname<\/td> <td>([^<>]*)<\/td>/", $contents, $teamNickname);
		preg_match("/<td >Team Motto<\/td> <td>([^<>]*)<\/td>/", $contents, $teamMotto);
		preg_match("/<td >Team Website<\/td> <td><a(?:[^>]*)?>([^<>]*)<\/a><\/td>/", $contents, $teamSite);
/*
		fb($teamName[1]);
		fb($teamLocation[1]);
		fb($teamRookieYear[1]);
		fb($teamNickname[1]);
		fb($teamMotto[1]);
		fb($teamSite[1]);
*/
		//TODO: add in regex for getting seasons
		/*    <td(?:[^>]*)?>(?:([^<>]*)|<br />)*</td>   */
		

		$db->team->update(
			array(
				"_id" => $obj['_id']
			),
			array(
				'$set' => array(
					'info' => array(
						"name" => $teamName[1],
						"site" => $teamSite[1],
						"nickname" => $teamNickname[1],
						"motto" => $teamMotto[1],
						"rookieYear" => $teamRookieYear[1],
						"location" => $teamLocation[1]
					),
					$events
				)
			),
			true
		);

		die();
	}

	send_reg(array('message' => 'finished getting team profiles'));
break;
default:
	send_error('invalid subRequest');
}


/*
Errorcheck
Recalculate
Reset DB

$stuff = $db->dataMiner->find();
$stuff = iterator_to_array($stuff);
fb($stuff);

foreach($stuff as $key) {
	echo json_decode($key[0]);
}
*/
?>