<?php
/*
Requires:
	subRequest - getTeams...
*/

//TODO: make function to limit history (number of years to track teams back to)


switch ($input['subRequest']) {
case "getTeams": //gets the number & tpid (used by FIRST to identify teams) for each team, then gets all profiles

	require 'php/scraper/teams.php';
	send_reg(['message' => 'finished getting team info']);

break;
case "getEvents": //get all events & add links for teams in each match (which will hold scouting data)

	require 'php/scraper/events.php';
	send_reg(['message' => 'finished getting team info']);

break;
case "updateFMS": //update scores/schedule of current or recent events (uses twitter)

	require "php/scraper/twitter.php";
	send_reg(['message' => 'finished updating FMS']);
	
break;
case "compile":
	
	require "php/admin/compile.php";
	send_reg(['message' => 'db is compiled']);

break;
case "rebuildAnalysisScouting":
	
	require "php/analysis.php";
	analysisScoutingRebuild();
	send_reg(['message' => 'scouting analysis db is rebuilt']);

break;
case "export":

	require "php/admin/export.php";
	send_reg(['message' => 'export is avaliable in /tmp/db/exports']);

break;
case "clearTmp": //clear out & rebuild tmp
	
	require 'maintenance/maintenance.php';
	clearTmp();
	send_reg(['message' => 'tmp directory is rebuilt']);

break;
case "clearLog": //clear out log collection in mongoDB
	
	$db->log->remove([]);
	send_reg(['message' => 'db log is cleared (except for this message)']);

break;
case "backupDB": //copy DB to file in tmp/backup

	send_error('not finished');
	require 'maintenance/maintenance.php';

break;
default:
	send_error('invalid subRequest');
}
?>