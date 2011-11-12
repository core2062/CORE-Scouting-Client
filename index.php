<?php

//TODO php code to assign embedded pages var (which may later be based on the user requesting the page), and put code for js var in page script area

?>

<!DOCTYPE html>
<html>	<!--TODO add  manifest="manifest.mf" + make file-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Input</title>
<link href="favicon.ico" rel="shortcut icon" id="favicon"/>
<!--[if lt IE 9]>
<link rel="stylesheet" type="text/css" href="css/style-ie.css" />
<![endif]-->
<link rel="stylesheet" href="css/style.css" /> <!-- embed into the page ? -->
<style>
	
	/* embed linked css (input, home...) w/ php */ 
	
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
			
			<!-- get nav bar stuff -->
			
			</ul>
			
			<hr style="margin:10px;" />

		
		</td>
	</tr>
	<!-- END Top-Bar -->
	
	<!-- START Content -->
	<tr>
		<td id="content">
	
		</td>
		
		<td id="sidebar" style="max-height:100%;">
			
			
			
			
			
			
			<div id="jGrowl-container" class="jGrowl bottom-right"></div>
			<!--TODO fix jGrowl positioning - make it flow with other content/no overlap (add pooling? ... or mechanisim to remove messages when there are too many? ... or scroll bar on message container - not including "close all" in scroll?) -->
			<!-- TODO create classes of jGrowl notifications to close selectively -->

		</td>
	</tr>
	<!-- END Content -->
	
	<!-- START Bottom Bar -->
	<tr id="foot">
		<td colspan="2">
			<g:plusone size="medium" style"float:left;"></g:plusone><!-- Google +1 Button -->
			<!-- TODO make a +Snippet https://developers.google.com/+/plugins/+1button/#plusonetag -->
			<div id="progressbar"> <!-- make JS code to turn on/off per page by var -->
				<div id="progressbar-value"></div>
				<div id="errorbar-value"></div>
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
<script src="script/script.js"></script>
<script type="text/javascript">

// embed script w/ php (form input, home ...)

</script>
</body>
</html>




<?php
//$html = ob_get_contents();
//ob_clean ();


//remove html commments
//strip double spaces from html (regex)
//strip out any space outside of tags ( divide by line break into array, loop through & trim(); & remove blank lines)
//optimize & embed css and js
//base64 images ?

?>