<?php

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

	clearTmp();
?>