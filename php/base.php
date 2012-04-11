<?php
//This script handles: basic functions needed for almost all scripts

function globalVar($name, $update = null){//consider adding ability to set var here too
	global $db;

	if(!isset($update)){
		$return = $db->globalVar->findOne(
			array(
				'_id' => $name
			)
		);
	} else {
		$return = $db->globalVar->update(
			array(
				'_id' => $name
			),
			array(
				'$set' => array(
					'value' => $update
				)
			),
			true
		);
	}
	return $return['value'];
}

function globalVarAppend($name, $newData){
	if(count($newData) != 0){
		$newData = array_merge(globalVar($name), $newData);
		globalVar($name, $newData);
	}
}

//logging function
$log = []; //start log - used for general logging (any messages that are not recorded by anything else)

function logger($message, $fbDisplay = false){
	global $log;
	global $starttime;
	global $vars;

	list($micro, $sec) = explode(" ",microtime());
	$duration = (float)$sec + (float)$micro - $starttime;

	$log[] = array($message, $duration);

	if($fbDisplay == true && $vars['devMode'] == true){
		fb($message);
	}

	error_log($message . ", at " . $duration . "\n", 3, "tmp/log");
}

function fileWrite($filename, $contents){
	$fp = fopen($filename, "w+");
	fwrite($fp, $contents);
	fclose($fp);
}

//global return functions
function send_error($error_text, $error = '', $script = ''){
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;
	global $user;
	global $place;
	
	if($script != ''){//if a script is defined, record it
		$log[] = 'script defined: ' . $script;
	}

	if($error == "") $error = $error_text;

	logger("script ended with error, output buffer=" . ob_get_contents());

	$db->log->insert(
		array(
			'type' => 'error',
			'errorcode' => $error,
			'place' => $place,
			'time' => $starttime,
			'input' => $input,
			'log' => $log,
			'vars' => $vars,
			'user' => $user
		)
	);

	ob_clean (); //empty output buffer, stuff below is only thing sent
	
	if($script == ''){
		die("{'error':'$error_text'}");
	} else {
		die("{'error':'$error_text', 'script':'$script'}");
	}
}

function send_reg($return = '',$enableEncode = true, $logReturn = true){
	global $db;
	global $starttime;
	global $log;
	global $input;
	global $vars;
	global $user;
	global $type;
	global $place;

	logger("script ended, output buffer=" . ob_get_contents());

	/*$db->log->insert(
		array(
			'type' => $type,
			'return' => $logReturn ? $return : "",
			'place' => $place,
			'time' => $starttime,
			'input' => $input,
			'log' => $log,
			'vars' => $vars,
			'user' => $user
		)
	);*/

	if($enableEncode == true){//option required for index.php (sends entire page as return)
		$return = json_encode($return);
	}
	
	ob_clean(); //empty output buffer, stuff below is only thing sent
	die($return);
}

//general functions
function array_add($arrays){//parameter (arrays) is a array of the arrays to be added (must all have same keys)
	//TODO: make this recursive
	$numOfArrays = count($arrays);
	$keys = array_keys($arrays[0]);
	$numOfKeys = count($keys);
	for($i=0; $i < $numOfKeys; $i++){
		$sum[$keys[$i]] = 0;
		for($e=0; $e < $numOfArrays; $e++){ 
			$sum[$keys[$i]] = $sum[$keys[$i]] + $arrays[$e][$keys[$i]];
		}
	}
	return $sum;
}

?>