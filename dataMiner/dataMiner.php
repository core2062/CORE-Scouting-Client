<?php
	// remove this section before production
	require_once('../FirePHP/fb.php');
	ob_start();

	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );

	//connect to mongoDB
	$m = new Mongo();
	$db = $m->selectDB("CSD");

	//get $sessionID
	$url = "https://my.usfirst.org/myarea/index.lasso?page=searchresults&skip_events=0&skip_teams=0&programs=FRC&season_FRC=2012&season_FTC=2011&season_FLL=2011&season_JFLL=2011&season_FP=2011&reports=teams&area=All&results_size=25";

	$contents = file_get_contents($url, false);

	preg_match("/<form action=\"index.lasso\?-session=myarea:([A-Za-z_0-9]*)\" method=\"post\">/", $contents, $matches);
	$sessionID = $matches[1];

	preg_match('/All Areas\s+\((....|...|..|.) found,/', $contents, $matches);
	$totalTeamsFound = $matches[1];

	$contents = "";//reuse variable below

	for($i = 0; $i < ($totalTeamsFound/250); $i++){
		$url = "https://my.usfirst.org/myarea/index.lasso?page=searchresults&results_size=250&omit_searchform=1&skip_teams=" . $i*250 . "&-session=myarea:" . $sessionID . "#FRC_teams";

		$temp = file_get_contents($url, false);

		//test if below improves regex speed
		$temp = preg_replace('/<!--(.|\s)*?-->/', '', $temp); //removes comments
		$temp = preg_replace('/\s+/', ' ',$temp); //removes double spaces, indents, and line breaks

		$contents .= $temp;
	}

/*
	$stuff = $db->dataMiner->find();
	$stuff = iterator_to_array($stuff);
	fb($stuff);

	foreach($stuff as $key) {
		echo json_decode($key[0]);
	}
*/

function processTeamEntry($input){
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



?>