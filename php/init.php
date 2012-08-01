<?php
//This script acts as a basic startup script for almost all scripts

//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

//dependencies & required files
if (version_compare(PHP_VERSION, '5.4') != 1) {
	die("you're gonna need php 5.4 to run this, sorry");
}
/*
if (!class_exists("Mongo")) {
	die("you're gonna need the php_mongo module to run this, sorry");
}
if(`ps -C mongod` == "  PID TTY          TIME CMD\n"){//test if mongod is running
	die("it doesn't look like mongod is running... you should look into this, try running mongod and view the output");
}
*/
//define mongo
/*
$m = new Mongo();
$db = $m->selectDB("csd");
*/
require 'php/base.php';

//check if dev mode is set (dev mode disables obfuscation / minification & caching)
if (isset($_GET['dev'])) {
	$vars['devMode'] = true;
} else {//check for global devMode
	$vars['devMode'] = globalVar('devMode');
}

if(globalVar('devMode')){
	require 'dev/firephp/fb.php';
	ob_start();

	error_reporting( E_ALL );
	ini_set('display_errors', 1);
}

if(!file_exists('tmp')){
	//NOTICE: if there are errors with tmp files, the entire directory should be removed (because this code will cause it to be rebuilt correctly)
	require 'php/maintenance/maintenance.php';
	clearTmp();
}

//get basic variables
$vars['ip'] = $_SERVER['REMOTE_ADDR'] or send_error("cannot get ip");

//The User Object (full example) -- referenced used in login script, and maybe others too later
$userObject = array(
	"_id"=> "SeanLang-2062",
	"permission"=> 9,
	"token"=> "4f1c860728df71.38499022",

	"info"=> array(//send
		"fName"=> "Sean",
		"lName"=> "Lang",
		"team"=> 2062
	),

	"prefs"=> array(//send, optional info
		"fade"=> true,
		"verbose"=> true
	),

	"account"=> array(//not sent, required info
		"pword"=> "superpass",
		"email"=> "slang800@gmail.com"
	),

	"stats"=> array(//not sent, created by server side
		"ip"=> "127.0.0.1",
		"logintime"=> 1327269383.167
	),
	
	"opt"=> array(//not sent, optional info
		"zip"=> 53072,
		"browser"=> "Firefox",
		"gender"=> "m"
	)
);
?>