<?php
/*
	PHP embeds pages based on user id, and popularity of page (can't actually see page hash requested)
	JS reads hash & searches the page for the sub-page requested
	if requested page is found, then that is presented, otherwise it is downloaded (from catche or from page.php)
	page.php takes necessary components to build the page and sends it to JS via AJAX
	

    Official Terms:

    Site: index.php, which acts as the container for all pages
    Page: a set of sub-pages, and modals within the site (like input, home, or query)
    Subpage: a piece of a page, only one subpage is displayed at a time
    Modal: a dialog which gets overlaid on top of the site, some pages require specific modals, and these are sent with the page request
    Base Page: page components which are needed for more than 1 page, these are embedded on first page request
    
	
	Main TODO
	
	TODO: make a kill IE 6 page like http://themeforest.net/item/kill-ie6-template/full_screen_preview/795628
	TODO: make a IE compatiable stylesheet
	TODO: add gzip to server
	TODO: make sure links on facebook contain a nice description of the site
	TODO: make signup page hidden when logged in (or just not sent when a valid cookie is given w/ request)
	TODO: make onload scripts for each page
	TODO: make a small dropdown for the nav button that gives the page categories
	TODO: move all js scripts & static resources to cookie-less sub-domain
	TODO: change table style back to table sorter style, then to aristo like
	TODO: make fonts store in cache
	TODO: make openid / login from other site
	TODO: move public to new page - out of member analysis?
	
	wiki style editing
	blue alliance like public side w/ only basic data (data gathering and analysis on member side)
	allow attachment of videos to matches (low priority - much later)
	
	use scoutID to determine embedded pages (besides base) - based on most accessed or permissions
	make jGrowl append to top of scrollable box
	
	track progress of AJAX download? - use green bar on progress bar to show download, or just for effect
	catche AJAX content
	store scouting data during error to a cookie - attempt to resubmit (ok if it's submitted twice) - delete cookie and display jGrowl when sent
	
	change popup to open link in new window or tab - opens a link to a page that retrieves & displays data from the cookie and contains instructions on how to save and resubmit data - or modal
	add offline scouting mode for competitions without internet access
	
	TODO: on login, check if training was finished for that page
	TODO: fix font rendering across browsers - check support for @fontface
	
	TODO: use standard deviation on DB
	TODO: use php to get possible competition list

	TODO: make a 404 page

	make a "upload direct button" (or something like that) in input-navbar (and a corresponding modal) to let people send POST data if AJAX failed first time

	message: paste the saved data into the box, and it will be sent to the database, do not modify the data in anyway or it will fail upon detecting out of range, or mis-formatted data. If it is not possible to send it from this computer... contact me/send it to me.

	check to see if save valuable scouting data jGrowl pops up for blank ScoutID or other errors

	change popup thing with scouting data POST string to a modal (or even better: downloadify type thing)

	add check to php: if data being entered is exactly the same (except for date) as another entry then allow it (or "overwrite" it) & move on

	make submit button disabled after sending (add class disabled?), until next error check is run (or something that prevents 2X clicking)

	make submit button disabled when form is blank

	add styling for disabled button

	TODO: provide link to github on site???

	if scouting entry matches a existing entry in some categories, but other areas of the original entry are blank, fill in blanks (used to pre-fill match robots & match numbers.

	if there is something that doesn't match (but match numbers are the same) then write the parts that don't match to a error field (and affects error count)

	above things are about compiled database table, in normal submit, data is written to row, no checking is done, blank fields are ignored. each row is a separate entry, even if they are duplicates.
	in complied table, all entries are searched and formed into a final table, all errors sorted out. 1 match in each row.

	//scouting 
	pickup balls from wall or bridge
	printout sheet - summary of alliance competing against
		log of important events (like robot dieing during match)

	scouting as full alliance
*/

$place = 'index.php';
$type = 'page-gen';//changed if page is loaded from cache
$version = 'alpha';

require 'php/general.php';


//Options
	
	//defaults
	$disableCache = false;
	$input['devMode'] = false;

	//check if dev mode is set (dev mode disables obfustication/minification & caching)
	if (isset($_GET['dev'])) {
		$input['devMode'] = true;
		$disableCache = true;
		//the cache will prevent some changes from appearing (because not everything is checked for modifications) & also, it cuts down on time (since developing involves changing & refreshing many times)
		fb('dev mode enabled');
	}

function checkForUser(){
	if(empty($_COOKIE['user']) == false){
		//variables_order must contain "C" in php.ini
		$input['user'] = json_decode($_COOKIE['user'], true);
		fb('user detected');

		if (empty($input['user']['_id']) == true) {
			send_error("scoutid was not receved",'','logout();');//cannot run logout() w/out scoutid
		}
		if (empty($input['user']['token']) == true) {
			logout("token was not receved");
		}

		//check user & assign user object
		$user = $db->user->findOne(
			array(
				'_id' => $input['user']['_id']
			)
		);

		if ($user['token'] !== $input['user']['token']) {//validate token
			logout("token is incorrect, you have been logged out for security reasons");
		}
		if ($user['stats']['ip'] !== $vars['ip']) {//validate ip address
			logout("ip is incorrect, you have been logged out for security reasons");
		}
		if ($user['permission'] == 0) {
			send_error('your account is banned', 'banned account');
		}


		$log[] = "admin page loaded";

		$disableCache = true;//cache will only hold general pages for now... pages w/ user specific changes are not cached
	}
}
checkForUser();


//get sitemap set vars for embedded pages & filename
	$pages = $db->siteMap->findOne();
	unset($pages['_id']);//remove id
	fb($pages);
	$len = count($pages);
	for($i=0; $i < $len; $i++){
		if($pages[$i]['embedded'] == true) {
			$embedded[] = $pages[$i]['name'];
		}
	}

	sort($embedded); //make sure that filename being searched for in cache is same, regardless of request order
	$embeddedLen = count($embedded);

	$filename = $embedded[0];
	for ($i = 1; $i < $embeddedLen; ++$i) {
		$filename .= "," . $embedded[$i];
	}
	$filename = 'cache/' . $filename . '-index';
	$log[] = "filename = " . $filename;


if (file_exists($filename) == true && $disableCache == false){//also, check if cache has been disabled

	//function to check if files have been modified
	function cacheCheck() {
		global $embeddedLen;
		global $embedded;
		global $filename;

		$cache_date = filemtime($filename);

		//sadly, navbar does not use the same naming scheme as the rest of the files (check separately)
		if (filemtime('html/navbar.html') > $cache_date) {return false;}

		$htmlparts = array('navbar', 'content', 'sidebar', 'modals');
		$parts_length = count($htmlparts);

		for ($i = 0; $i < $embeddedLen; ++$i) {
			$file = 'css/' . $embedded[$i] . '.css';
			if (file_exists($file)) {
				if (filemtime($file) > $cache_date) {return false;}
			}
		}
		for ($i = 0; $i < $embeddedLen; ++$i) {
			$file = 'script/' . $embedded[$i] . '.js';
			if (file_exists($file)) {
				if (filemtime($file) > $cache_date) {return false;}
			}
		}
		for ($e = 0; $e < $parts_length; ++$e) {
			for ($i = 0; $i < $embeddedLen; ++$i) {
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

include 'php/jsminplus.php';
?>

<!DOCTYPE html>
<html> <!--TODO: add  manifest="manifest.mf" + make file-->
<head>
<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
<title>Input</title>
<link href="favicon.ico" rel="shortcut icon" id="favicon"/>
<!--[if lt IE 9]>
<link rel="stylesheet" type="text/css" href="css/style-ie.css" />
<![endif]-->
<style>
	<?php
		embed('css/', '.css');
		//TODO: add in csstidy + fix gradient support
	?>
</style>
</head>
<body id="body">
<!--TODO: add tags for bookmarks and/or for search engines -->


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
<div class="modal-aligner">
	<div class="modal-wrapper">
		<div style="display: none;" id="modal-container">
			<div class="modal-titlebar">
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


<!--TODO: embed jquery, fix issues w/ being in same file -->
<script type="text/javascript" src="script/jquery.js"></script>
<script type="text/javascript">

<?php

function embed($folder, $extension) {
	global $embeddedLen;
	global $embedded;
	global $pages;

	for ($i = 0; $i < $embeddedLen; ++$i) {
		$file = $folder . $embedded[$i] . $extension;

		if (file_exists($file) == true) {
			include($file);
		}
	}
}

$html = ob_get_contents();
ob_clean ();//fix to prevent send_reg from putting the entire page in the log

if ($input['devMode'] == false) {
	$html = preg_replace('/<!--(.|\s)*?-->/', '', $html); //removes comments
	$html = preg_replace('/\s+/', ' ',$html); //removes double spaces, indents, and line breaks
	//$html = preg_replace('/\s</', '<',$html); // removes spaces between tags (this causes some weird issues)
}

//remove extra stuff from $pages
$len = count($pages);
for($i=0; $i < $len; $i++){
	unset($pages[$i]['embedded']);
}

$javascript = 'var pages = ' . json_encode($pages); //or put jquery in at this point: file_get_contents('script/jquery.js')
for ($i = 0; $i < $embeddedLen; ++$i) {
	$file = 'script/' . $embedded[$i] . '.js';

	if (file_exists($file) == true) {
		$javascript .= file_get_contents($file);
	}
}
if ($input['devMode'] == false){
	$javascript = JSMinPlus::minify($javascript);
}

$html = $html . $javascript . '</script></body></html>';

//optimize css here
//TODO: use php to base64 images

$html = trim($html);

//cache data to temporary file (unless it is disabled)
if($disableCache == false){
	$fp = fopen($filename, "w+");
	fwrite($fp, $html);
	fclose($fp);
}

send_reg($html, false, false);
?>