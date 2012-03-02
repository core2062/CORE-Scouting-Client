<?php
/*
Requires:
	subRequest - getTeams, getTeamProfiles...
*/

//get $sessionID
//TODO: make sessionID getter more robust/low level

function getSessionID(){
	global $sessionID;
	global $contents; //so it can be reused in getTeams

	$year = 2012;//year to get data from
	$contents = file_get_contents("https://my.usfirst.org/myarea/index.lasso?page=searchresults&skip_teams=0&programs=FRC&season_FRC=" . $year . "&reports=teams&results_size=25", false);
	preg_match("/<form action=\"index.lasso\?-session=myarea:([A-Za-z_0-9]*)\" method=\"post\">/", $contents, $matches);
	$sessionID = $matches[1];
	logger("got/updated session key");
}

if($input['subRequest'] == 'getTeams' || $input['subRequest'] == 'getTeamProfiles'){
	getSessionID();
}

switch ($input['subRequest']) {
case "getTeams": //gets the number & tpid of each team

	//find out how many teams were returned (to determine # of pages) - use same content as sessionID getter
	preg_match('/All Areas\s+\((....|...|..|.) found,/', $contents, $matches);
	$totalTeamsFound = $matches[1];

	$contents = "";//reuse variable below

	//get a bunch of pages from the FIRST site 
	for($i = 0; $i < ($totalTeamsFound/250); $i++){
		$url = "https://my.usfirst.org/myarea/index.lasso?page=searchresults&results_size=250&omit_searchform=1&skip_teams=" . $i*250 . "&-session=myarea:" . $sessionID . "#FRC_teams";

		$temp = file_get_contents($url, false);

		//test if below improves regex speed
		$temp = preg_replace('/<!--(.|\s)*?-->/', '', $temp); //removes comments
		$temp = preg_replace('/\s+/', ' ',$temp); //removes double spaces, indents, and line breaks

		$contents .= $temp;
	}

	//process the pages using a regex, to get the tpid (used by FIRST to identify teams) for each team in that year's competition
	function processTeamEntry($input){//add each team to DB
		global $db;

		$db->team->update(
			array(
				"_id" => (int)$input[2]
			),
			array(
				'$set' => array(
					"meta" => array(
						"tpid" => (int)$input[1]
					)
				)
			),
			true
		);
		return "";//is this needed or assumed????? & in below
	}

	$regex = '/<a href="\?page=team_details&tpid=(.....)&amp;-session=myarea:'. $sessionID . '"><b>(....|...|..|.)<\/b><\/a>/';
	preg_replace_callback($regex, "processTeamEntry", $contents);

	send_reg(array('message' => 'finished getting the number and tpid of each team'));
	
break;
case "getTeamProfiles": //get profiles of each team. requires a tpid for each team
	ini_set('max_execution_time', 90); //this script needs extra time ?????
	//TODO: is above needed

	$cursor = $db->team->find()->sort(array("_id" => 1));

	//define the function - actually get used at end of team-get 
	function processEvent($input){//add each team to DB
		global $db;
		global $obj;

		unset($input[0]);//this isn't needed
		$input = array_values($input);//reset keys in array

		//decode html characters
		$len = count($input);
		for ($i=1; $i < $len; $i++) {// first value is year of competition -- no special characters
			$input[$i] = html_entity_decode($input[$i]);
		}

		$input['awards'] = array();

		//split awards string into array
		for ($i=2; $i < $len; $i++) {// award list starts at 2nd array element
			$input['awards'] = (array)explode("<br />", $input[$i]) + $input['awards'];
			unset($input[$i]);
		}

		//search for blank values in awards array & remove extra whitespace
		$len = count($input['awards']);
		for ($i=0; $i < $len; $i++) {
			$input['awards'][$i] = trim($input['awards'][$i]);
			if(empty($input['awards'][$i]) || $input['awards'][$i] == '<i>(2000 and prior award listings may be incomplete)</i>'){
				unset($input['awards'][$i]);
			}
		}

		$input['awards'] = array_values($input['awards']);//reset keys in array

		$input[1] = str_replace($input[0] . ' ', '', $input[1]);//remove year prefix

		//finally add to DB
		//TODO: find faster way to do this (w/out copying and re-adding)
		$events = $db->team->find(
			array(
				'_id' => $obj['_id']
			),
			array(
				'events' => 1
			)
		);

		$events = iterator_to_array($events);

		//if(!empty($events[$obj['_id']]['events'])){
			$events = $events[$obj['_id']]['events'];//really only need this part
		//}
		
		//TODO: make year be integer

		$events[$input[0]][$input[1]]['awards'] = $input['awards'];

		$db->team->update(
			array(
				"_id" => $obj['_id']
			),
			array(
				'$set' => array(
					"events" => $events
				)
			),
			true
		);
		return "";
	}

	$count = 0;//after x teams, get new sessionID

	//TODO: switch to seperate processes to built cache of pages to process & then extract data (low priority)
	foreach($cursor as $obj){

		if($count > 200){
			getSessionID();
			$count = 0;
		}

		logger('getting team:' . $obj['_id']);

		$url = "https://my.usfirst.org/myarea/index.lasso?page=team_details&tpid=" . $obj['meta']['tpid'] . "&-session=myarea:" . $sessionID;
		$contents = file_get_contents($url, false);//TODO: add way to re-try if connection times out 

		$contents = preg_replace('/(?:(v)?align="[a-z]*"|nowrap|bgcolor="#......"|width="..(?:.)?%"|<!--(.|\s)*?-->)/', '', $contents); //removes comments double spaces, indents, line breaks, and other crap
		$contents = preg_replace('/\s+/', ' ',$contents); //removes 

		preg_match("/<td >Team Name<\/td> <td>([^<>]*)<\/td>/", $contents, $team['name']);
		preg_match("/<td >Team Location<\/td> <td>([^<>]*)<\/td>/", $contents, $team['location']);
		preg_match("/<td >Rookie Season<\/td> <td>(....)<\/td>/", $contents, $team['rookieYear']);
		preg_match("/<td >Team Nickname<\/td> <td>([^<>]*)<\/td>/", $contents, $team['nickname']);
		preg_match("/<td >Team Motto<\/td> <td>([^<>]*)<\/td>/", $contents, $team['motto']);
		preg_match("/<td >Team Website<\/td> <td><a(?:[^>]*)?>([^<>]*)<\/a><\/td>/", $contents, $team['site']);
		
		//TODO: change to using a single object that gets inserted - low priority

		//all those pregs had 1 backreferance, this moves the matches to proper place in array
		//also decode them
		foreach ($team as $key => $value) {
			//TODO: fix issue with undefined index (not deadly)
			$team[$key] = $team[$key][1];
		}

		settype($team['rookieYear'], "int");

		$team['name'] = utf8_encode(html_entity_decode($team['name']));
		$team['location'] = utf8_encode(html_entity_decode($team['location']));
		$team['nickname'] = utf8_encode(html_entity_decode($team['nickname']));
		$team['motto'] = utf8_encode(html_entity_decode($team['motto']));

		$db->team->update(
			array(
				"_id" => $obj['_id']
			),
			array(
				'$set' => array(
					'info' => $team
				)
			),
			true
		);

		preg_replace_callback("/<tr > <td >([^<>]*)<\/td> <td >([^<>]*)<\/td> <td >((?:[^<>]*|<br \/>|<(?:\/)?i>)*)<\/td> <\/tr>/", "processEvent", $contents);

		$count++;
	}

	send_reg(array('message' => 'finished getting team profiles'));
break;
case "getEvents": //get all events & add links for teams in each match (which will hold scouting data)

break;
case "updateEvents": //update scores/schedule of current or recent events

break;
default:
	send_error('invalid subRequest');
}


/*
Errorcheck
Recalculate
Reset DB

$stuff = $db->dataMiner->find();
$stuff = iterator_to_array($stuff);
fb($stuff);

foreach($stuff as $key) {
	echo json_decode($key[0]);
}
*/
/*
array(
	'&apos;'=>'&#39;',
	'&minus;'=>'&#45;',
	'&circ;'=>'&#94;',
	'&tilde;'=>'&#126;',
	'&Scaron;'=>'&#138;',
	'&lsaquo;'=>'&#139;',
	'&OElig;'=>'&#140;',
	'&lsquo;'=>'&#145;',
	'&rsquo;'=>'&#146;',
	'&ldquo;'=>'&#147;',
	'&rdquo;'=>'&#148;',
	'&bull;'=>'&#149;',
	'&ndash;'=>'&#150;',
	'&mdash;'=>'&#151;',
	'&tilde;'=>'&#152;',
	'&trade;'=>'&#153;',
	'&scaron;'=>'&#154;',
	'&rsaquo;'=>'&#155;',
	'&oelig;'=>'&#156;',
	'&Yuml;'=>'&#159;',
	'&yuml;'=>'&#255;',
	'&OElig;'=>'&#338;',
	'&oelig;'=>'&#339;',
	'&Scaron;'=>'&#352;',
	'&scaron;'=>'&#353;',
	'&Yuml;'=>'&#376;',
	'&fnof;'=>'&#402;',
	'&circ;'=>'&#710;',
	'&tilde;'=>'&#732;',
	'&Alpha;'=>'&#913;',
	'&Beta;'=>'&#914;',
	'&Gamma;'=>'&#915;',
	'&Delta;'=>'&#916;',
	'&Epsilon;'=>'&#917;',
	'&Zeta;'=>'&#918;',
	'&Eta;'=>'&#919;',
	'&Theta;'=>'&#920;',
	'&Iota;'=>'&#921;',
	'&Kappa;'=>'&#922;',
	'&Lambda;'=>'&#923;',
	'&Mu;'=>'&#924;',
	'&Nu;'=>'&#925;',
	'&Xi;'=>'&#926;',
	'&Omicron;'=>'&#927;',
	'&Pi;'=>'&#928;',
	'&Rho;'=>'&#929;',
	'&Sigma;'=>'&#931;',
	'&Tau;'=>'&#932;',
	'&Upsilon;'=>'&#933;',
	'&Phi;'=>'&#934;',
	'&Chi;'=>'&#935;',
	'&Psi;'=>'&#936;',
	'&Omega;'=>'&#937;',
	'&alpha;'=>'&#945;',
	'&beta;'=>'&#946;',
	'&gamma;'=>'&#947;',
	'&delta;'=>'&#948;',
	'&epsilon;'=>'&#949;',
	'&zeta;'=>'&#950;',
	'&eta;'=>'&#951;',
	'&theta;'=>'&#952;',
	'&iota;'=>'&#953;',
	'&kappa;'=>'&#954;',
	'&lambda;'=>'&#955;',
	'&mu;'=>'&#956;',
	'&nu;'=>'&#957;',
	'&xi;'=>'&#958;',
	'&omicron;'=>'&#959;',
	'&pi;'=>'&#960;',
	'&rho;'=>'&#961;',
	'&sigmaf;'=>'&#962;',
	'&sigma;'=>'&#963;',
	'&tau;'=>'&#964;',
	'&upsilon;'=>'&#965;',
	'&phi;'=>'&#966;',
	'&chi;'=>'&#967;',
	'&psi;'=>'&#968;',
	'&omega;'=>'&#969;',
	'&thetasym;'=>'&#977;',
	'&upsih;'=>'&#978;',
	'&piv;'=>'&#982;',
	'&ensp;'=>'&#8194;',
	'&emsp;'=>'&#8195;',
	'&thinsp;'=>'&#8201;',
	'&zwnj;'=>'&#8204;',
	'&zwj;'=>'&#8205;',
	'&lrm;'=>'&#8206;',
	'&rlm;'=>'&#8207;',
	'&ndash;'=>'&#8211;',
	'&mdash;'=>'&#8212;',
	'&lsquo;'=>'&#8216;',
	'&rsquo;'=>'&#8217;',
	'&sbquo;'=>'&#8218;',
	'&ldquo;'=>'&#8220;',
	'&rdquo;'=>'&#8221;',
	'&bdquo;'=>'&#8222;',
	'&dagger;'=>'&#8224;',
	'&Dagger;'=>'&#8225;',
	'&bull;'=>'&#8226;',
	'&hellip;'=>'&#8230;',
	'&permil;'=>'&#8240;',
	'&prime;'=>'&#8242;',
	'&Prime;'=>'&#8243;',
	'&lsaquo;'=>'&#8249;',
	'&rsaquo;'=>'&#8250;',
	'&oline;'=>'&#8254;',
	'&frasl;'=>'&#8260;',
	'&euro;'=>'&#8364;',
	'&image;'=>'&#8465;',
	'&weierp;'=>'&#8472;',
	'&real;'=>'&#8476;',
	'&trade;'=>'&#8482;',
	'&alefsym;'=>'&#8501;',
	'&larr;'=>'&#8592;',
	'&uarr;'=>'&#8593;',
	'&rarr;'=>'&#8594;',
	'&darr;'=>'&#8595;',
	'&harr;'=>'&#8596;',
	'&crarr;'=>'&#8629;',
	'&lArr;'=>'&#8656;',
	'&uArr;'=>'&#8657;',
	'&rArr;'=>'&#8658;',
	'&dArr;'=>'&#8659;',
	'&hArr;'=>'&#8660;',
	'&forall;'=>'&#8704;',
	'&part;'=>'&#8706;',
	'&exist;'=>'&#8707;',
	'&empty;'=>'&#8709;',
	'&nabla;'=>'&#8711;',
	'&isin;'=>'&#8712;',
	'&notin;'=>'&#8713;',
	'&ni;'=>'&#8715;',
	'&prod;'=>'&#8719;',
	'&sum;'=>'&#8721;',
	'&minus;'=>'&#8722;',
	'&lowast;'=>'&#8727;',
	'&radic;'=>'&#8730;',
	'&prop;'=>'&#8733;',
	'&infin;'=>'&#8734;',
	'&ang;'=>'&#8736;',
	'&and;'=>'&#8743;',
	'&or;'=>'&#8744;',
	'&cap;'=>'&#8745;',
	'&cup;'=>'&#8746;',
	'&int;'=>'&#8747;',
	'&there4;'=>'&#8756;',
	'&sim;'=>'&#8764;',
	'&cong;'=>'&#8773;',
	'&asymp;'=>'&#8776;',
	'&ne;'=>'&#8800;',
	'&equiv;'=>'&#8801;',
	'&le;'=>'&#8804;',
	'&ge;'=>'&#8805;',
	'&sub;'=>'&#8834;',
	'&sup;'=>'&#8835;',
	'&nsub;'=>'&#8836;',
	'&sube;'=>'&#8838;',
	'&supe;'=>'&#8839;',
	'&oplus;'=>'&#8853;',
	'&otimes;'=>'&#8855;',
	'&perp;'=>'&#8869;',
	'&sdot;'=>'&#8901;',
	'&lceil;'=>'&#8968;',
	'&rceil;'=>'&#8969;',
	'&lfloor;'=>'&#8970;',
	'&rfloor;'=>'&#8971;',
	'&lang;'=>'&#9001;',
	'&rang;'=>'&#9002;',
	'&loz;'=>'&#9674;',
	'&spades;'=>'&#9824;',
	'&clubs;'=>'&#9827;',
	'&hearts;'=>'&#9829;',
	'&diams;'=>'&#9830;'
)
*/
?>