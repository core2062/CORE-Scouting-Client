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
	Modal: a dialog which gets overlayed on top of the site, some pages require specific modals, and these are sent with the page request
	Base Page: page components which are needed for more than 1 page, these are embedded on first page request
-->

<?php
require_once('FirePHP/fb.php');
ob_start();

error_reporting( E_ALL ); // TODO remove before production
ini_set( 'display_errors', 1 ); // TODO remove before production


//check if files were modified & get temp file if they were not

//TODO php code to assign embedded pages var (which may later be based on the user requesting the page), and put code for js var in page script area

$embedded[0] = "base";


fb($embedded);
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
		$length = count($embedded);
		for ($i = 0; $i < $length; ++$i) {
			getfile('css/' . $embedded[$i] . '.css');
		}
	?>

</style>

</head>
<body>
<!-- TODO add version number... probably at bottom of page -->
<!-- TODO add data for bookmarks -->


<!-- START Layout -->
<table id="layout">
	
	<!-- START Top-Bar -->
	<tr id="head">
		<td colspan="2">
			<ul style="padding:10px 0 0 0; margin:0 0 0 10px;">
			
				<?php
					$length = count($embedded);
					for ($i = 0; $i < $length; ++$i) {
						getfile('html/' . $embedded[$i] . '-navbar.html');
					}
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
				$length = count($embedded);
				for ($i = 0; $i < $length; ++$i) {
					getfile('html/' . $embedded[$i] . '-content.html');
				}
			?>
	
		</td>
		
		<td id="sidebar" style="max-height:100%;">
			
			
			<?php
				$length = count($embedded);
				for ($i = 0; $i < $length; ++$i) {
					getfile('html/' . $embedded[$i] . '-sidebar.html');
				}
			?>
			
			
			
			<div id="jGrowl-container" class="jGrowl bottom-right"></div>
			<!--TODO fix jGrowl positioning - make it flow with other content/no overlap (add pooling? ... or mechanisim to remove messages when there are too many? ... or scroll bar on message container - not including "close all" in scroll?) -->
			<!-- TODO create classes of jGrowl notifications to close selectively -->

		</td>
	</tr>
	<!-- END Content -->
	
	<!-- START Bottom Bar -->
	<tr id="foot">
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
	
	<!-- get Modal Content w/ php -->

	<div class="modal-buttons ui-widget-content ui-helper-clearfix"> <!-- TODO get rid of these classes, i hate them -->
		<button type="button" onclick="modalclose();">Close</button> <!-- TODO make enter button close modal -->
	</div>

</div>

<div class="overlay Navagation-c Login-c Contact-c" onclick="modalclose();"></div>
<!-- END Modals -->

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script> <!-- FIRST teams only exist in countries that can access google, no hosting issues -->
<script src="script/base.js"></script>
<script type="text/javascript">

// embed script w/ php (form input, home ...), do after html processing
  
  </script>
</body>  
</html>  




<?php

function getfile($file) {
	if (file_exists($file) == true) {
		echo file_get_contents($file);
	}
}

$html = ob_get_contents();
ob_clean (); //empty output buffer


$html = preg_replace('/<!--(.|\s)*?-->/', '', $html); //removes comments
$html = preg_replace('/\s+/', ' ',$html); //removes double spaces, indents, and line breaks
$html = preg_replace('/\> </', '><',$html); // removes spaces between tags

//optimize & embed css and js (must be after html processing)
//base64 images ?

//fb('hello world');

die($html);

?>