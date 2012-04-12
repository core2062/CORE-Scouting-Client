<?php
/*
	This script handles all requests for pages that come from the user (requests that come from js are over in other php scripts, depending on their type). It is responsible for interpreting the request, pulling all parts of the UI together, and sending it out.
*/

$place = 'index.php';
$type = 'page-gen';//changed if page is loaded from cache
$version = 'alpha';

require 'php/init.php';//TODO: add error reporting if mongodb is down

//TODO: add function to check if db is setup (can be commented out later)

empty($_SERVER["HTTP_REFERER"]) ? $vars["referrer"] = "not found" : $vars["referrer"] = $_SERVER["HTTP_REFERER"];

//get site-map
	require 'php/siteMap.php';
	if($vars['devMode'] == true) fb($pages);

//options
	//defaults
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

	$filename = $embedded[0];
	$len = count($embedded);
	for ($i = 1; $i < $len; ++$i) {
		$filename .= "," . $embedded[$i];
	}
	$filename = 'tmp/pages/' . $filename . '-index';
	logger("filename = " . $filename);


if (file_exists($filename) == true && $vars['disableCache'] == false){//also, check if cache has been disabled

	//function to check if files have been modified
	function cacheCheck() {
		global $embedded;
		global $filename;

		$cache_date = filemtime($filename);

		//sadly, navbar does not use the same naming scheme as the rest of the files (check separately)
		if (filemtime('html/navbar.html') > $cache_date) {return false;}

		$htmlparts = array('navbar', 'content', 'sidebar', 'modals');
		$parts_length = count($htmlparts);

		$len = count($embedded);
		for ($i = 0; $i < $len; ++$i) {
			$file = 'tmp/css/' . $embedded[$i] . '.css';
			if (file_exists($file)) {
				if (filemtime($file) > $cache_date) {return false;}
			}
		}
		for ($i = 0; $i < $len; ++$i) {
			$file = 'tmp/js/' . $embedded[$i] . '.js';
			if (file_exists($file)) {
				if (filemtime($file) > $cache_date) {return false;}
			}
		}
		for ($e = 0; $e < $parts_length; ++$e) {
			for ($i = 0; $i < $len; ++$i) {
				$file = 'html/' . $embedded[$i] . '-' . $htmlparts[$e] . '.html';
				if (file_exists($file)) {
					if (filemtime($file) > $cache_date) {return false;}
				}
			}
		}
		return true;
	}

	//code to get cached file and send it
	if (cacheCheck() === true) {
		$html = file_get_contents($filename);
		fb('cached');

		$type = 'page-cache';
		send_reg($html, false, false);
	}
}

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

//TODO: rewrite below html to PATH
echo '<!DOCTYPE html>';
/*
$path =
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
		'<!--[if lt IE 9]><link rel="stylesheet" type="text/css" href="css/style-ie.css" /><![endif]-->',
		['style', embed('css/', '.css')],
		//TODO: add meta tags for bookmarks and/or for search engines
	],
	['body#body'
	
	]
]*/
?>

<html> <!--TODO: add manifest="manifest.mf" + make file-->
<head>
	<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
	<title>CSD</title>
	<link href="favicon.ico" rel="shortcut icon" id="favicon"/>
	<!--[if lt IE 9]>
	<link rel="stylesheet" type="text/css" href="css/style-ie.css" />
	<![endif]-->
	<style>
		<?php
			function getCSS(){
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
				//$css->set_cfg('remove_last_;',TRUE);
				$css->parse($cssCode);
				echo $css->print->plain();
			}
			getCSS()
		?>
	</style>
	<!--TODO: add meta tags for bookmarks and/or for search engines -->
</head>
<body id="body">



<!-- START Layout -->
<table id="layout">

	<!-- START Top-Bar -->
	<tr class="head">
		<td colspan="2">

			<?php
				include('html/navbar.html');
			?>

			<hr style="margin:10px 145px 10px 10px" />

		</td>
	</tr>
	<!-- END Top-Bar -->

	<!-- START Content -->
	<tr>
		<td id="content">
			<noscript>
				<p>Um, this is awkward&hellip; all navigation (and most functionality) on this site relies on JavaScript, a programming language that helps add interactivity to web sites. So I can't really do anything until you enable it. If you don't know how to enable JavaScript, look <a href="http://www.activatejavascript.org">here</a>.</p>
			</noscript>

			<?php
				embed('html/', '-content.html');
			?>

		</td>

		<td id="sidebar" style="max-height:100%;">

			<?php
				embed('html/', '-sidebar.html');
			?>

			<div id="jGrowl-container" class="jGrowl bottom-right"></div>
			<!--TODO: fix jGrowl positioning - align with bottom of side bar (add pooling? ... or mechanism to remove messages when there are too many? ... or scroll bar on message container - not including "close all" in scroll?) -->
			<!-- TODO: create classes of jGrowl notifications to close selectively -->
		</td>
	</tr>
	<!-- END Content -->

	<!-- START Bottom Bar -->
	<tr class="foot">
		<td colspan="2">

			<!-- Google +1 Button -->
			<div class="g-plusone" data-size="medium" callback="plusone();" data-href="www.urlofmysite.com"></div>
			<!-- TODO: make a +Snippet https://developers.google.com/+/plugins/+1button/#plusonetag -->

			<div id="progressbar">
				<div id="progressbar-value"></div>
				<div id="errorbar-value"></div>
			</div>

			<div style="left: 25%; position: relative; width: 50%; font-size: 12px; text-align: center;">
				<p>CORE Scouting Database - Created By <a href="#contact">Sean Lang</a> - &copy;<?php echo date('Y');?> - version: <?php echo $version;?></p>
			</div>
		</td>
	</tr>
	<!-- END Bottom Bar -->

</table>
<!-- END Layout -->


<!-- START Modals -->
<div id="modal-aligner">
	<div id="modal-wrapper">
		<div style="display: none;" id="modal-container">
			<div id="modal-titlebar">
				<span id="modal-title">Title</span>

				<a onclick="modalClose();" class="close"> <!--TODO: remove the extra a tag ?-->
					<span class="icon icon-closethick"></span>
				</a>
			</div>

			<?php
				embed('html/', '-modals.html');
			?>

			<div id="modal-buttons">
				<button type="button" style="display: none;" class="navigation-c contact-c credits-c edit-account-c" onclick="modalClose();">Close</button>
				<button type="button" style="display: none;" class="login-c" onclick="getToken();">Login</button>
				<button type="button" style="display: none;" class="login-c" onclick="window.location = '#signup'">Create Account</button>
				<button type="button" style="display: none;" class="login-c" onclick="window.location = '#documentation'">Help</button><!-- TODO: make help button work -->
				<button type="button" style="display: none;" class="account-c" onclick="modalClose()">Save</button>
				<button type="button" style="display: none;" class="contact-c" onclick="sendMessage()">Send</button>
			</div>
		</div>
	</div>
</div>

<div id="overlay" onclick="modalClose();"></div>
<!-- END Modals -->

<script type="text/javascript">
<?php

function embed($folder, $extension) {
	global $embedded;
	global $pages;
	global $vars;

	$embeddedLen = count($embedded);
	for ($embeddedIndex = 0; $embeddedIndex < $embeddedLen; ++$embeddedIndex) {
		$file = $folder . $embedded[$embeddedIndex] . $extension;

		if(file_exists($file)){
			require($file);
			if($vars['devMode']) logger($file . ' was embedded', true);
		} else {
			if($vars['devMode']) logger($file . ' is non-existent', true);
		}
	}
}

$html = ob_get_contents();//TODO: remove after PATH conversion
ob_clean();//fix to prevent send_reg from putting the entire page in the log

if ($vars['devMode'] == false) {//TODO: remove after PATH conversion
	$html = preg_replace('/<!--(.|\s)*?-->/', '', $html); //removes comments
	$html = preg_replace('/\s+/', ' ',$html); //removes double spaces, indents, and line breaks
	//$html = preg_replace('/\s</', '<',$html); // removes spaces between tags (this causes some weird issues)
}

//remove extra stuff from $pages
$len = count($pages);
for($i=0; $i < $len; $i++){
	unset($pages[$i]['embedded']);
}

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

function embedJavaScript(){
	global $embedded;
	global $pages;
	global $vars;

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

	//system("rm -rf " . $cwd . "/tmp/coffee");//remove temporary coffee files

	if($vars['devMode'] == false){//TODO: finish fixing this
		require 'dev/jsminplus.php';
		$minified = JSMinPlus::minify($javascript);
		fb($minified);
	}

	return $javascript;
}

$html .= embedJavaScript() . "\n</script></body></html>";

//TODO: use php to base64 images

$html = trim($html);//remove a little more whitespace

//cache data to temporary file (unless it is disabled)
if($vars['disableCache'] == false){
	fileWrite($filename, $html);
}

send_reg($html, false, false);
?>