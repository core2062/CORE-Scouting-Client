<?php
/*
	This script handles all requests for pages that come from the user (requests that come from js are over in other php scripts, depending on their type). It is responsible for interpreting the request, pulling all parts of the UI together, and sending it out.
*/

$place = 'index.php';
$type = 'page-gen';//changed if page is loaded from cache
$version = 'alpha';

require 'php/init.php';//TODO: add error reporting if mongodb is down
require 'php/path.php';

$html = new path;

//TODO: add function to check if db is setup (can be commented out later)

empty($_SERVER["HTTP_REFERER"]) ? $vars["referrer"] = "not found" : $vars["referrer"] = $_SERVER["HTTP_REFERER"];

//get site-map
require 'php/siteMap.php';
if($vars['devMode'] == true) fb($pages);

//options
$vars['disableCache'] = false;

if($vars['devMode'] == true){
	$vars['disableCache'] = true;//the cache will prevent some changes from appearing (because not everything is checked for modifications) & also, it cuts down on time (since developing involves changing & refreshing many times)
	logger('dev mode enabled');
}

function checkForUser(){
	global $db;
	global $vars;
	global $pages;

	if(empty($_COOKIE['user']) == false){

		//variables_order must contain "C" in php.ini
		$user = json_decode($_COOKIE['user'], true);

		//if user object is wrong, return & move on with guest-level functionality

		if(empty($user['_id']) == true || empty($user['token']) == true) {
			logger("checkForUser failed while getting basic parameters");
			return;
		}

		//check user & assign user object
		$user = $db->user->findOne(
			array(
				'_id' => $user['_id']
			)
		);

		if($user['stats']['ip'] !== $vars['ip'] || $user['permission'] == 0 || $user['token'] !== $user['token']) {//validate user object
			logger("checkForUser failed on user object validation");
			unset($user);
			return;
		}

		//embed admin page if admin
		if($user['permission'] == 9){
			$pages[1]['embedded'] = true;//show admin page
			$pages[1]['hidden'] = false;
			logger("admin page loaded");
			$vars['disableCache'] = true;//cache will only hold general pages for now... pages w/ user specific changes are not cached
		}
	}
}
checkForUser();


//set vars for embedded pages & filename
$len = count($pages);
for($i=0; $i < $len; $i++){
	if($pages[$i]['hidden'] == true){
		unset($pages[$i]);
	} elseif($pages[$i]['embedded'] == true) {
		$embedded[] = $pages[$i]['name'];
	}
}

$pages = array_values($pages);//reindex from unsetting

sort($embedded); //make sure that filename being searched for in cache is same, regardless of request order

$embedded = array_diff($embedded,['base']);//remove base
array_unshift($embedded,'base');//put it back in at beginning of array

$filename = $embedded[0];
$len = count($embedded);
for ($i = 1; $i < $len; ++$i) {
	$filename .= "," . $embedded[$i];
}
$filename = 'tmp/pages/' . $filename . '-index';
logger("filename = " . $filename);

/*
if (file_exists($filename) == true && $vars['disableCache'] == false){

	//function to check if files have been modified
	function cacheCheck() {//FIXME: this is broken
		global $embedded;
		global $filename;

		$cache_date = filemtime($filename);

		//sadly, navbar does not use the same naming scheme as the rest of the files (check separately)
		if (filemtime('path/navbar.path') > $cache_date) {return false;}

		$len = count($embedded);
		for ($i = 0; $i < $len; ++$i) {
			$file = 'tmp/css/' . $embedded[$i] . '.css';
			if (file_exists($file)) {
				if (filemtime($file) > $cache_date) {return false;}
			}
		}
		for ($i = 0; $i < $len; ++$i) {
			$file = 'coffee/' . $embedded[$i] . '.coffee';
			if (file_exists($file)) {
				if (filemtime($file) > $cache_date) {return false;}
			}
		}
		for ($i = 0; $i < $len; ++$i) {
			$file = 'path/' . $embedded[$i] . '.path';
			if (file_exists($file)) {
				if (filemtime($file) > $cache_date) {return false;}
			}
		}
		return true;
	}

	//code to get cached file and send it
	if(cacheCheck() === true){
		$html = file_get_contents($filename);
		fb('cached');

		$type = 'page-cache';
		send_reg($html, false, false);
	}
}
*/

if($vars['devMode']){
	//TODO: move? only needed in dev mode
	require 'dev/lessphp/lessc.inc.php';
	function compileLess(){
		$handle = opendir('less');

		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				preg_match('/[a-z]*\./', $entry, $entry);//cut off file extension
				if(!empty($entry)){//checks if it has no file extension (like a directory)
					try {
						lessc::ccompile('less/' . $entry[0] . 'less', 'tmp/css/' . $entry[0] . 'css');
					} catch (exception $ex) {
						logger($ex->getMessage(),true);
					}
				}

			}
		}
		closedir($handle);
	}
	compileLess();
}

$html->path =
['html',//TODO: add manifest="manifest.mf" + make file
	['head',
		['meta',
			'http-equiv' => "Content-Type",
			'content' => "text/html",
			'charset' => "utf-8"
		],
		['title', 'CSD'],
		['link#favicon',
			'href' => 'favicon.ico',
			'rel' => 'shortcut icon',
		],
		'<!--[if lt IE 9]><link rel="stylesheet" type="text/css" href="css/style-ie.css" /><![endif]-->',//TODO: add IE style sheet (low priority)
		['style#:style',
			function (){
				global $embedded;
				global $pages;
				global $vars;

				$putBackLater = ob_get_contents();
				ob_clean();//this part of the script uses the output buffer to hold the unprocessed css temporarly

				$embeddedLen = count($embedded);
				for ($embeddedIndex = 0; $embeddedIndex < $embeddedLen; ++$embeddedIndex) {
					$file = 'tmp/css/' . $embedded[$embeddedIndex] . '.css';

					if(file_exists($file)){
						require($file);
						if($vars['devMode']) logger($file . ' was embedded', true);
					} else {
						if($vars['devMode']) logger($file . ' is non-existent', true);
					}
				}

				$cssCode = ob_get_contents();
				ob_clean();
				echo $putBackLater;

				require('dev/csstidy/class.csstidy.php');

				$css = new csstidy();
				$css->parse($cssCode);
				return [$css->print->plain()];
			}
		],
		//TODO: add meta tags for bookmarks and/or for search engines
	],
	['body#body',
		['table#layout',
			['tr#head',//top bar
				//TODO:put navbar here
			],
			['tr',
				['td#content',
					['noscript',
						['p',
							'Um, this is awkward&hellip; all navigation (and most functionality) on this site relies on JavaScript, a programming language that helps add interactivity to web sites. So I can\'t really do anything until you enable it. If you don\'t know how to enable JavaScript, look <a href="http://www.activatejavascript.org">here</a>.'
						]
					],
					//TODO: put content here
				],
				['td#sidebar',
					'style'=>'max-height:100%',
					//TODO:put sidebar stuff here
					['#jGrowl-container.jGrowl bottom-right']
					//TODO: fix jGrowl positioning - align with bottom of side bar (add pooling? ... or mechanism to remove messages when there are too many? ... or scroll bar on message container - not including "close all" in scroll?)
					//TODO: create classes of jGrowl notifications to close selectively
				],
				['tr#foot',
					['td',
						'colspan'=>2,
						['.g-plusone','data-size'=>"medium",'callback'=>"plusone()",'data-href'=>"www.urlofmysite.com"],//Google +1 Button
						//TODO: make a +Snippet https://developers.google.com/+/plugins/+1button/#plusonetag
						['#progressbar',
							['#progressbar-value'],
							['#errorbar-value']
						],
						['div',
							'style'=>"left: 25%; position: relative; width: 50%; font-size: 12px; text-align: center",
							['p','CORE Scouting Database - Created By <a href="#contact">Sean Lang</a> - &copy;' . date('Y') . ' - version:' . $version]
						]
					]
				]
			]
		],
		['#modalAligner',
			['#modalWrapper',
				['#modalContainer',
					'style'=>"display: none",
					['#modalTitlebar',
						['span#modalTitle','Title'],
						['a.close',//remove the extra a tag ?
							'onclick'=>'modalClose()',
							['span.icon icon-closethick']
						]
					],
					['temp#modalContent'],
					['#modalButtons']
				]
			]
		],
		['#overlay','onclick'=>'modalClose()'],
		['script',
			'type'=>"text/javascript",
			function(){
				global $embedded;
				global $pages;
				global $vars;
				
				//remove extra stuff from $pages
				$len = count($pages);
				for($i=0; $i < $len; $i++){
					unset($pages[$i]['embedded']);
				}

				$javascript = 'var pages = ' . json_encode($pages) . ';';//embed pages
				$coffeeFiles = '';

				ob_clean();//this part of the script uses the output buffer to hold the unprocessed coffee script temporarly

				$cwd = getcwd();
				mkdir($cwd . "/tmp/coffee");

				$len = count($embedded);
				for($i = 0; $i < $len; ++$i){
					$file = $embedded[$i] . '.coffee';

					if (file_exists('coffee/' . $file) == true) {
						$tmpFile = 'tmp/coffee/' . $file;
						$fileContents = getCoffee($file);
						file_put_contents($tmpFile, $fileContents);
						$coffeeFiles .= ' ' . $tmpFile;
						ob_clean();//this part of the script uses the output buffer to hold the unprocessed coffee script temporarly
					}
				}		

				exec('/home/sean/bin/coffee -pj tmp/coffee' . $coffeeFiles, $output);//not sure why join requires a file... this doesn't get anything written to it but it must be there

				$javascript .= implode("\n",$output);

				system("rm -rf " . $cwd . "/tmp/coffee");//remove temporary coffee files

				#if($vars['devMode'] == false){
					require 'dev/jsminplus.php';
					$javascript = JSMinPlus::minify($javascript);
				#}

				return [$javascript];
			}
		]
	]
];

require('path/navbar.path');

//set vars for needed parts of page (used to append content to these areas)
$content = &$html->find('#content');
$sidebar = &$html->find('#sidebar');
$modalContent = &$html->find('#modalContent');
$modalButtons = &$html->find('#modalButtons');

$len = count($embedded);
for($i = 0; $i < $len; ++$i){
	$includeFile = 'path/' . $embedded[$i] . '.path';
	if (file_exists($includeFile)){
		require($includeFile);
		if($vars['devMode']) logger($includeFile . ' was embedded', true);
	} else {
		if($vars['devMode']) logger($includeFile . ' is non-existent', true);
	}
}

$html = '<!DOCTYPE html>' . $html->compile();

//embed javascript - TODO: test and fix
//TODO: rewrite get coffee so it doesn't use the output buffer
function getCoffee($file){//this function can be called in the coffee files to get other dependancies
	$file = 'coffee/' . $file;

	$oldOutputBuffer = ob_get_contents();
	ob_clean();
	require $file;
	$fileContents = ob_get_contents();
	ob_clean();
	echo $oldOutputBuffer;//put the old contents back

	preg_match('/\.(coffee|js)/', $file, $fileExtension);//get file extension
	$fileExtension = $fileExtension[1];
	
	if($fileExtension == 'js'){
		return '`' . $fileContents . '`';//use backticks to pass js through coffee script compiler
	} else {
		return $fileContents;//use backticks to pass js through coffee script compiler
	}
}

//TODO: use php to base64 images

//cache data to temporary file (unless it is disabled)
if($vars['disableCache'] == false){
	file_put_contents($filename, $html);
}

send_reg($html, false, false);
?>