<?php
/*
Requires:
	subRequest - getTeams...
*/

//TODO: make function to limit history (number of years to track teams back to)

$vars['devMode'] = globalVar('devMode');

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
case "compile": //clear out log collection in mongoDB
	
	require "php/admin/compile.php";
	send_reg(['message' => 'db is compiled']);

break;
case "rebuildAnalysisScouting": //clear out log collection in mongoDB
	
	require "php/analysis.php";
	analysisScouting();
	send_reg(['message' => 'scouting analysis db is rebuilt']);

break;
case "export":

	require "php/admin/export.php";
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
	require "php/admin/resetDB.php";

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