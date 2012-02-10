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
		$db->dataMiner->insert(
			array(
				"_id" => (int)$input[2],
				"meta" => array(
					"tpid" => (int)$input[1]
				)
			)
		);
	}

	$regex = '/<a href="\?page=team_details&tpid=(.....)&amp;-session=myarea:'. $sessionID . '"><b>(....|...|..|.)<\/b><\/a>/';
	preg_replace_callback($regex, "processTeamEntry", $contents);
	
break;
case "getTeamProfiles": //get profiles of each team. requires a tpid for each team
	
	$stuff = $db->dataMiner->find();
	$stuff = iterator_to_array($stuff);
	fb($stuff);
	/*
	foreach($stuff as $key) {
		echo json_decode($key[0]);
	}
	*/
	
break;
default:
	send_error('invalid inputType','');
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