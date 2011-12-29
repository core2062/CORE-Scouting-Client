<?php
 // remove this section before production
require_once('FirePHP/fb.php');
ob_start();

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
?>


<!--
    PHP embeds pages based on user id, and popularity of page (can't actually see page hash requested)
    JS reads hash & searches the page for the sub-page requested
    if requested page is found, then that is presented, otherwise it is downloaded (from catche or from page.php)
    page.php takes necessary components to build the page and sends it to JS via AJAX
-->

<!--
    Offical Terms:

    Site: index.php, which acts as the container for all pages
    Page: a set of sub-pages, and modals within the site (like input, home, or query)
    Subpage: a piece of a page, only one subpage is displayed at a time
    Modal: a dialog which gets overlaid on top of the site, some pages require specific modals, and these are sent with the page request
    Base Page: page components which are needed for more than 1 page, these are embedded on first page request
-->

<!-- TODO make a kill IE 6 page like http://themeforest.net/item/kill-ie6-template/full_screen_preview/795628 -->
<!-- TODO add gzip to server -->
<!-- TODO make sure links on facebook contain a nice description of the site -->


<?php
//start timer
list($micro, $sec) = explode(" ",microtime());
$starttime = (float)$sec + (float)$micro;

$pages = array(
array(
    "name"=> "home",
	"embedded"=> true,
	"full-name"=> "Home",
    "description"=> "lorem",
    "subpages"=> array(
        "front-page"=> array(
			"full-name"=> "Front Page",
            "description"=> "lorem",
            "login-required"=> false
        ),
        "synopsis"=> array(
			"full-name"=> "Synopsis",
            "description"=> "lorem",
            "login-required"=> false
        ),
        "tour"=> array(
			"full-name"=> "Guided Tour",
            "description"=> "lorem",
            "login-required"=> false
        ),
        "signup"=> array(
			"full-name"=> "Signup",
            "description"=> "lorem",
            "login-required"=> false
        )
    ),
    "modals"=> array(),
    "minWidth"=> "1150px",
    "progressbar"=> "none"
), array(
    "name"=> "input",
	"embedded"=> true,
	"full-name"=> "Input",
    "description"=> "lorem",
    "subpages"=> array(
        "robot"=> array(
			"full-name"=> "Robot Scouting",
            "description"=> "lorem",
            "login-required"=> true
        ),
        "human"=> array(
			"full-name"=> "Human Scouting",
            "description"=> "lorem",
            "login-required"=> true
        ),
        "pit"=> array(
			"full-name"=> "Pit Scouting",
            "description"=> "lorem",
            "login-required"=> true
        )
    ),
    "modals"=> array(),
    "minWidth"=> "1150px",
    "progressbar"=> "block"
), array(
    "name"=> "analysis",
	"embedded"=> false,
	"full-name"=> "Analysis",
    "description"=> "lorem",
    "subpages"=> array(
        "public"=> array(
			"full-name"=> "Public Analysis",
            "description"=> "lorem",
            "login-required"=> false
        ),
        "member"=> array(
			"full-name"=> "Member Analysis",
            "description"=> "lorem",
            "login-required"=> true
        ),
        "data-liberation"=> array(
			"full-name"=> "Data Liberation",
            "description"=> "lorem",
            "login-required"=> true
        )
    ),
    "modals"=> array(),
    "minWidth"=> "1150px",
    "progressbar"=> "none"
), array(
    "name"=> "team-leader",
	"embedded"=> false,
	"full-name"=> "Team Leader",
    "description"=> "lorem",
    "subpages"=> array(
        "manage"=> array(
			"full-name"=> "Manage Scouting",
            "description"=> "lorem",
            "login-required"=> false
        ),
        "contribution"=> array(
			"full-name"=> "View Contribution",
            "description"=> "lorem",
            "login-required"=> false
        ),
        "team"=> array(
			"full-name"=> "View Team Members",
            "description"=> "lorem",
            "login-required"=> false
        )
    ),
    "modals"=> array(),
    "minWidth"=> "1150px",
    "progressbar"=> "none"
), array(
    "name"=> "help",
	"embedded"=> false,
	"full-name"=> "Help",
    "description"=> "lorem",
    "subpages"=> array(
        "training"=> array(
			"full-name"=> "Manage Training",
            "description"=> "lorem",
            "login-required"=> true
        ),
        "documentation"=> array(
			"full-name"=> "Documentation",
            "description"=> "lorem",
            "login-required"=> false
        ),
        "forum"=> array(
			"full-name"=> "Forum",
            "description"=> "lorem",
            "login-required"=> true
        )
    ),
    "modals"=> array(),
    "minWidth"=> "1150px",
    "progressbar"=> "none"
), array(
	"name"=> "base",
	"embedded"=> true,
	"full-name"=> "Other",
	"description"=> "",
	"subpages"=> array(),
	"modals"=> array(
		"navigation"=> array(
			"full-name"=> "Navigation",
	        "login-required"=> false
        ),
        "login"=> array(
			"full-name"=> "Login",
            "login-required"=> false
        ),
        "contact"=> array(
			"full-name"=> "Contact",
            "login-required"=> false
        ),
        "credits"=> array(
			"full-name"=> "Credits",
            "login-required"=> false
        ),
        "account"=> array(
			"full-name"=> "Edit Account",
	        "login-required"=> true
        )
    ),
    "minWidth"=> "1150px",
    "progressbar"=> "none"
)
);
//TODO move public to new page - out of member analysis?

$len = count($pages);
for($i=0; $i < $len; $i++){
	if($pages[$i]['embedded'] == true) {
		$embedded[] = $pages[$i]['name'];
	}
}

fb($embedded);

sort($embedded); //make sure that filename being searched for in cache is same, regardless of request order
$embeddedLen = count($embedded);



$m = new Mongo(); // connect
$db = $m->selectDB("CSD");

if (isset($_GET['dev'])) {
	$dev = true;
	fb('dev mode enabled');
} else {
	$dev = false;
}
//TODO require authentication to use dev, or just remove before production

if (isset($_COOKIE['scoutid'])) {
	$input['scoutid'] = $_COOKIE['scoutid'];
}
$input['ip'] = $_SERVER['REMOTE_ADDR'];

//TODO add code to check id sent with cookie to determine if admin page can be sent with request
//TODO maybe add some logging here



$filename = $embedded[0];
for ($i = 1; $i < $embeddedLen; ++$i) {
	$filename .= "," . $embedded[$i];
}
$filename = 'cache/' . $filename . '-index';

if (file_exists($filename) == true){
	$htmlparts[0] = 'navbar';
	$htmlparts[1] = 'content';
	$htmlparts[2] = 'sidebar';
	$htmlparts[3] = 'modals';

	$parts_length = count($htmlparts);

	$cache_date = filemtime($filename);

	//check if files have been modified
	function cache_check() {
		global $embeddedLen;
		global $htmlparts;
		global $parts_length;
		global $cache_date;
		global $embedded;
		global $dev;

		if ($dev == true){
			return false;
		}

		if (filemtime('html/navbar.html') > $cache_date) {return false;}

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
	if (cache_check() === true) {
		$html = file_get_contents($filename);
		fb('cached');

		list($micro, $sec) = explode(" ",microtime());
		$endtime = (float)$sec + (float)$micro;
		$total_time = ($endtime - $starttime);

		$log_input = json_encode($input);

		$insert = "{
			type:'page-cache',
			place:'index.php',
			time:'$starttime',
			duration:'$total_time',
			filename:'$filename',
			input:$log_input
		}";

		$db->execute("db.log.insert($insert)");

		ob_clean (); //empty output buffer
		die($html);
	}
}

include 'php/jsminplus.php';
?>

<!DOCTYPE html>
<html> <!--TODO add  manifest="manifest.mf" + make file-->
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
		//TODO add in csstidy + fix gradient support
	?>
</style>
</head>
<body id="body">
<!--TODO add tags for bookmarks and/or for search engines -->


<!-- START Layout -->
<table id="layout">

	<!-- START Top-Bar -->
	<tr class="head">
		<td colspan="2">

			<?php
				include('html/navbar.html');
			?>

			<hr style="margin:10px;" />

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
			<!--TODO fix jGrowl positioning - align with bottom of side bar (add pooling? ... or mechanisim to remove messages when there are too many? ... or scroll bar on message container - not including "close all" in scroll?) -->
			<!-- TODO create classes of jGrowl notifications to close selectively -->

			<span id="testbox" style="float: left">x</span><!-- test width of char -->
		</td>
	</tr>
	<!-- END Content -->

	<!-- START Bottom Bar -->
	<tr class="foot">
		<td colspan="2">

			<!-- Google +1 Button -->
			<div class="g-plusone" data-size="medium" callback="plusone();" data-href="www.urlofmysite.com"></div>
			<!-- TODO make a +Snippet https://developers.google.com/+/plugins/+1button/#plusonetag -->

			<div id="progressbar">
				<div id="progressbar-value"></div>
				<div id="errorbar-value"></div>
			</div>

			<div style="left: 25%; position: relative; width: 50%; font-size: 12px; text-align: center;">
				<p>CORE Scouting Database - Created By <a href="#contact">Sean Lang</a> - &copy;2011 - version: alpha</p>
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

				<a onclick="modalclose();" class="close"> <!--TODO fix this ... it fucks up the url -->
					<span class="icon icon-closethick"></span>
				</a>
			</div>

				<?php
					embed('html/', '-modals.html');
				?>

			<div id="modal-buttons">
				<button type="button" style="display: none;" class="navigation-c contact-c credits-c edit-account-c" onclick="modalclose();">Close</button>
				<button type="button" style="display: none;" class="login-c" onclick="login();">Login</button>
				<button type="button" style="display: none;" class="login-c" onclick="window.location = '#signup'">Create Account</button>
				<button type="button" style="display: none;" class="login-c" onclick="">Help</button><!-- TODO make help button work -->
			</div>
		</div>
	</div>
</div>

<div id="overlay" onclick="modalclose();"></div>
<!-- END Modals -->


<!--TODO embed jquery, fix issues w/ being in same file -->
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

if ($dev == false) {
	$html = preg_replace('/<!--(.|\s)*?-->/', '', $html); //removes comments
	$html = preg_replace('/\s+/', ' ',$html); //removes double spaces, indents, and line breaks
	//$html = preg_replace('/\s</', '<',$html); // removes spaces between tags

	//TODO fix the last command
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
if ($dev == false){
	$javascript = JSMinPlus::minify($javascript);
}

$html = $html . $javascript . '</script></body></html>';

//optimize css
//TODO base64 images w/ php ?

$html = trim($html);

//cache data to temporary file
$fp = fopen($filename, "w+");
fwrite($fp, $html);
fclose($fp);

list($micro, $sec) = explode(" ",microtime());
$endtime = (float)$sec + (float)$micro;
$total_time = ($endtime - $starttime);

$log_input = json_encode($input);

$insert = "{
	type:'page-gen',
	place:'index.php',
	time:'$starttime',
	duration:'$total_time',
	filename:'$filename',
	dev:'$dev',
	input:$log_input
}";

$db->execute("db.log.insert($insert)");

ob_clean ();
die($html);
?>





<!-- TODO make a 404 page -->

<!-- make a "upload direct button" (or something like that) in input-navbar (and a corresponding modal) to let people send POST data if AJAX failed first time -->

message: paste the saved data into the box, and it will be sent to the database, do not modify the data in anyway or it will fail upon detecting out of range, or misformatted data. only send data from the computer it was generated from or the database will reject it (IF TOKEN BASED LOGIN). If it is not possible to send it from this computer... contact me/send it to me.

check to see if save valuable scouting data jGrowl pops up for blank ScoutID or other errors

change popup thing with scouting data POST string to a modal (or even better: downloadify type thing)

add check to php: if data being entered is exactly the same (except for date) as another entry then allow it (or "overwrite" it) & move on

make submit button disabled after sending (add class disabled?), until next error check is run (or something that prevents 2X clicking)

make submit button disabled when form is blank

add styling for disabled button


if scouting entry matches a existing entry in some categories, but other areas of the original entry are blank, fill in blanks (used to pre-fill match robots & match numbers.

if there is something that doesn't match (but match numbers are the same) then write the parts that don't match to a error field (and affects error count)

above things are about compiled database table, in normal submit, data is written to row, no checking is done, blank fields are ignored. each row is a seperate entry, even if they are duplicates.
in complied table, all entries are searched and formed into a final table, all errors sorted out. 1 match in each row.


make data sent by input page be in JSON

remove convert to table thing in process, instead send JSON to client, and on client side convert to table.
