<?php

function getSessionID(){
	//TODO: make this more robust/low level & faster

	global $year;

	$contents = file_get_contents("https://my.usfirst.org/myarea/index.lasso?page=searchresults&skip_teams=0&programs=FRC&season_FRC=" . $year . "&reports=teams&results_size=25", false);
	preg_match("/<form action=\"index.lasso\?-session=myarea:([A-Za-z_0-9]*)\" method=\"post\">/", $contents, $matches);
	$sessionID = $matches[1];
	logger("got/updated session key");
	return $sessionID;
}

$year = 2012;//TODO: make year var more semantic

?>