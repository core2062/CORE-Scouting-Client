<?php
	/*
	This script sets up or resets the entire CSD database.
	It is not a part of any other script, and must be called carefully as it will remove all data on the site and restore the default user.
	*/

	//TODO: add auto backup

	if($_GET['pword'] != 'superpass') die('you need to prove your competency by correctly setting the pword GET var to the chosen pword in your request');
	
	$m = new Mongo();
	$m->selectDb("csd")->execute("function(){}");//creates database csd
	$db = $m->selectDB("csd");

	//general
	$db->createCollection("user");

	$db->user->insert(
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
			"value" => 1
		)
	);
	$db->globalVar->insert(
		array(
			"_id" => "devMode",//sets global devMode, if true, will override local devMode (especally for index.php & firebug)
			"value" => false
		)
	);
	$db->globalVar->insert(
		array(
			"_id" => "blacklist",//teams that are literally not worth my cpu cycles
			"value" => []
		)
	);
	$db->globalVar->insert(
		array(
			"_id" => "analysisScoutingErrors",//error log for analysisScouting
			"value" => []
		)
	);


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
?>