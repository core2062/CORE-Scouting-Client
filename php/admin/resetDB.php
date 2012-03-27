<?php
	/*
	This script sets up or resets the entire CSD database.
	It is not a part of any other script, and must be called carefully as it will remove all data on the site and restore the default user.
	*/

	//TODO: add auto backup

	if(is_set($_GET['pword']) && $_GET['pword'] == 'superpass'){
		echo 'resetting...';
	} else {
		die('you need to prove your competency by correctly setting the pword GET var to the chosen pword in your request');
	}
	
	$m = new Mongo();
	$m->selectDb("csd")->execute("function(){}");//creates database csd
	$db = $m->selectDB("csd");

	require '/../base.php';
	require '/../maintenance/maintenance.php';

	//general
	$db->createCollection("user");

	$db->user->insert(
		[
			"_id"=> "admin",
			"permission"=> 9,
			"token"=> "",
			"info"=> [
				"fName"=> "Sean",
				"lName"=> "Lang",
				"team"=> 2062
			],
			"prefs"=> [
				"fade"=> true,
				"verbose"=> true
			],
			"account"=> [
				"pword"=> "superpass",
				"email"=> "slang800@gmail.com"
			],
			"stats"=> [
				"ip"=> "",
				"logintime"=> 0
			],
			"opt"=> [
				"zip"=> 0,
				"browser"=> "Firefox",
				"gender"=> "m"
			]
		]
	);

	$db->createCollection("log");
	$db->createCollection("globalVar");

	globalVar("since_id", 1);//for updateFMS (used while interacting with twitter)
	globalVar("devMode", false);//sets global devMode, if true, will override local devMode (especially for index.php & firebug)
	globalVar("blacklist", []);//teams that are literally not worth my cpu cycles
	globalVar("analysisScoutingErrors", []);//error log for analysisScouting
	globalVar("analysisQueryLimits", []);//limit what is carried into analysisScouting

	//compiled collections (holds fully compiled data and is rebuilt (for now) because data relies on multiple sources)
	$db->createCollection("compiledEvent");
	$db->createCollection("compiledTeam");

	//analysis collections (holds semi-compiled data and is updated rather than rebuilt)
	$db->createCollection("analysisScouting");

	//source collections (holds nearly raw data)
	$db->createCollection("sourceScouting");
	$db->createCollection("sourceFMS");
	$db->createCollection("sourceTeamInfo");
	$db->createCollection("sourceEventInfo");

	clearTmp();//also rebuilds tmp

	echo ' done';
?>