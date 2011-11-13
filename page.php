<?php
require_once('FirePHP/fb.php');

// ex: http://localhost/CSD/page.php?page=input

$page=$_GET["page"];
//TODO maybe add some logging here
?>

<root>
	<navbar>
		<?php ?>
	</navbar>
	
	<content>
		<?php ?>
	</content>
	
	<modals>
		<?php ?>
	</modals>
	
	<js>
		<?php ?>
	</js>
	
	<css>
		<?php ?>
	</css>
	
	
</root>

<?php
$html = ob_get_contents();
ob_clean (); //empty output buffer


$html = preg_replace('/<!--(.|\s)*?-->/', '', $html); //removes comments
$html = preg_replace('/\s+/', ' ',$html); //removes double spaces, indents, and line breaks
$html = preg_replace('/\> </', '><',$html); // removes spaces between tags

//optimize & embed css and js (must be after html processing)
//base64 images ?

//make temp file

fb($page);

die($html);

?>