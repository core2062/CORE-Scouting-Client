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
<!-- TODO change MySQL to MongoDB -->

<?php
// TODO make code check if files were modified & get temp file if they were not

$embedded[0] = "base";
$embedded[1] = "home";
$embedded[2] = "input";

sort($embedded); //make sure that filename being searched for in cache is same, regardless of request order

//TODO maybe add some logging here

$length = count($embedded);


$filename = $embedded[0];
for ($i = 1; $i < $length; ++$i) {
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
		global $length;
		global $htmlparts;
		global $parts_length;
		global $cache_date;
		global $embedded;
		
		for ($i = 0; $i < $length; ++$i) {
			$file = 'css/' . $embedded[$i] . '.css';
			if (file_exists($file)) {
				if (filemtime($file) > $cache_date) {return false;}
			}
		}
		for ($i = 0; $i < $length; ++$i) {
			$file = 'script/' . $embedded[$i] . '.js';
			if (file_exists($file)) {
				if (filemtime($file) > $cache_date) {return false;}
			}
		}
		for ($e = 0; $e < $parts_length; ++$e) {
			for ($i = 0; $i < $length; ++$i) {
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
		$html = file_get_contents ($filename);
		fb('cached');
		ob_clean (); //empty output buffer
		die($html);
	}
}

include 'php/jsminplus.php';
?>

<!DOCTYPE html>
<html>	<!--TODO add  manifest="manifest.mf" + make file-->
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
<body>
<!-- TODO add version number... probably at bottom of page -->
<!-- TODO add data for bookmarks -->


<!-- START Layout -->
<table id="layout">
	
	<!-- START Top-Bar -->
	<tr class="head">
		<td colspan="2">
			<ul style="padding:10px 0 0 0; margin:0 0 0 10px;">
			
				<?php
					embed('html/', '-navbar.html');
				?>
			
			</ul>
			
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
			<!--TODO fix jGrowl positioning - make it flow with other content/no overlap (add pooling? ... or mechanisim to remove messages when there are too many? ... or scroll bar on message container - not including "close all" in scroll?) -->
			<!-- TODO create classes of jGrowl notifications to close selectively -->

		</td>
	</tr>
	<!-- END Content -->
	
	<!-- START Bottom Bar -->
	<tr class="foot">
		<td colspan="2" style="height:20px;">

			<!-- Google +1 Button -->
			<div class="g-plusone" data-size="medium" callback="plusone();" data-href="www.urlofmysite.com"></div>
			<!-- TODO make a +Snippet https://developers.google.com/+/plugins/+1button/#plusonetag -->
			
			<div id="progressbar"> <!-- make JS code to turn on/off per page by var -->
				<div id="progressbar-value"></div>
				<div id="errorbar-value"></div>
			</div>
			
			<div style="left: 25%; position: relative; width: 50%; margin-top: -14px; font-size: 12px; text-align: center; z-index: -1;">
				<p>CORE Scouting Database - Created By <a href="#" onclick="$('.contact-modal').dialog('open');">Sean Lang</a> - &copy;2011 - version: alpha</p>
			</div>
		</td>
	</tr>
	<!-- END Bottom Bar -->

</table>
<!-- END Layout -->




<!-- START Modals -->

<div style="display: none;" class="modal-container Navagation-c Login-c Contact-c">

	<div class="modal-titlebar ui-widget-header ui-helper-clearfix">
		<span id="modal-title">Title</span>
		
		<a href="#" class="close"> <!--TODO fix this ... it fucks up the url --> 
			<span class="icon icon-closethick"></span>
		</a>
	</div>
	
		<?php
			embed('html/', '-modals.html');
		?>

	<div class="modal-buttons ui-widget-content ui-helper-clearfix"> <!-- TODO get rid of these classes, i hate them -->
		<button type="button" onclick="modalclose();">Close</button> <!-- TODO make enter button close modal -->
	</div>

</div>

<div class="overlay Navagation-c Login-c Contact-c" onclick="modalclose();"></div>
<!-- END Modals -->

<script src="script/jquery.min.js"></script>
<script type="text/javascript"></script>
</body>  
</html>  




<?php

function embed($folder, $extension) {
	global $length;
	global $embedded;
	
	for ($i = 0; $i < $length; ++$i) {
		$file = $folder . $embedded[$i] . $extension;
		
		if (file_exists($file) == true) {
			echo file_get_contents($file);
		}
	}
}

$html = ob_get_contents();

$html = preg_replace('/<!--(.|\s)*?-->/', '', $html); //removes comments
$html = preg_replace('/\s+/', ' ',$html); //removes double spaces, indents, and line breaks
//$html = preg_replace('/\s</', '<',$html); // removes spaces between tags

//TODO fix the last command

$javascript = '';
for ($i = 0; $i < $length; ++$i) {
	$file = 'script/' . $embedded[$i] . '.js';

	if (file_exists($file) == true) {
		$javascript .= JSMinPlus::minify(file_get_contents($file));
	}
}
$html = preg_replace('/<script type="text\/javascript"><\/script>/', '<script type="text/javascript">' . $javascript . '</script>', $html);

//optimize css
//base64 images ?

$html = trim($html);

//cache data to temporary file
$fp = fopen($filename, "w+"); 
fwrite($fp, $html); 
fclose($fp); 

ob_clean (); //empty output buffer
die($html);
?>