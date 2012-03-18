<?php

$db->sourceFMS->remove([]);//clear out sourceFMS

$since_id = globalVar('since_id');
$since_id = 132931435354005504;

$fmsFiltered = [];

for($i = 1; $i < 17; $i++){
	$filename = "tmp/db/updateFMS/" . $i;

	logger('getting page ' . $i);

	logger(file_exists($filename));
	logger($vars['devMode']);
	logger($since_id);

	if(file_exists($filename) && $vars['devMode']){//check if page is in cache (caching is disabled if not in dev mode)
		logger('getting from cache');

		$fmsFeed = file_get_contents($filename);
	} else {
		logger('getting from twitter');

		$fmsFeed = file_get_contents('http://twitter.com/statuses/user_timeline/frcfms.json?trim_user=true&count=200&since_id=' . $since_id . "&page=" . $i, false);

		logger($fmsFeed);

		if($vars['devMode']){
			$fp = fopen($filename, "w+");
			fwrite($fp, $fmsFeed);
			fclose($fp);
		}
	}

	if($fmsFeed == '[]'){
		logger('reached end of loop (page was blank)');
		break;
	}

	$fmsFeed = json_decode($fmsFeed, true);

	foreach($fmsFeed as $obj){//$i = 0; $i > $len; $i++
		$obj['id'] = (float)$obj['id'];

		preg_match('/#FRC([^\s]*)/', $obj['text'], $info['eventCode']);
		preg_match('/TY (P|Q|E)/', $obj['text'], $info['matchType']);
		preg_match('/MC ([0-9]*)/', $obj['text'], $info['matchNumber']);

		preg_match('/RF ([0-9]*)/', $obj['text'], $info['redFinalScore']);
		preg_match('/BF ([0-9]*)/', $obj['text'], $info['blueFinalScore']);

		preg_match('/RA ([0-9]*) ([0-9]*) ([0-9]*)/', $obj['text'], $info['redTeams']);
		$info['redTeams'][1] = [(int)$info['redTeams'][1], (int)$info['redTeams'][2], (int)$info['redTeams'][3]];

		preg_match('/BA ([0-9]*) ([0-9]*) ([0-9]*)/', $obj['text'], $info['blueTeams']);
		$info['blueTeams'][1] = [(int)$info['blueTeams'][1], (int)$info['blueTeams'][2], (int)$info['blueTeams'][3]];

		preg_match('/RB ([0-9]*)/', $obj['text'], $info['redBridgePoints']);
		preg_match('/BB ([0-9]*)/', $obj['text'], $info['blueBridgePoints']);

		preg_match('/RFP ([0-9]*)/', $obj['text'], $info['redFoulPoints']);
		preg_match('/BFP ([0-9]*)/', $obj['text'], $info['blueFoulPoints']);

		preg_match('/RHS ([0-9]*)/', $obj['text'], $info['redHybridBasketPoints']);
		preg_match('/BHS ([0-9]*)/', $obj['text'], $info['blueHybridBasketPoints']);

		preg_match('/RTS ([0-9]*)/', $obj['text'], $info['redTeleopBasketPoints']);
		preg_match('/BTS ([0-9]*)/', $obj['text'], $info['blueTeleopBasketPoints']);

		preg_match('/CS (0|1|2)/', $obj['text'], $info['coopertitionPoints']);

		foreach ($info as $key => $value) {
			if(!empty($info[$key][1])){//fixes issue with undefined index... happens becasuse one of the above wasn't found
				$info[$key] = $info[$key][1];
			} else {
				$info[$key] = '';
			}
			if(is_numeric($info[$key])) $info[$key] = $info[$key] + 0;//change type of vars if they are actually numbers
		}

		$db->sourceFMS->update(
			[
				"_id" => $obj['id']
			],
			[
				'$set' => [
					//'text' => $obj['text'],
					'data' => $info
				]
			],
			true
		);

		if($obj['id'] > $since_id){
			$newSinceId = $obj['id'];
		}
	}
}

globalVar('sinceId', $newSinceId);

?>