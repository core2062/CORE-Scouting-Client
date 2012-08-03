<?php
//This script acts as a basic startup script for almost all scripts

require 'php/config.php';
require 'php/base.php';

//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

//dependencies & required files
if (version_compare(PHP_VERSION, '5.4') != 1) {
	die("you're gonna need php 5.4 to run this, sorry");
}

if(isset($_GET['dev'])){
	$vars['devMode'] = true;
}

//check if dev mode is set
if ($vars['devMode'] == true) {
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

?>