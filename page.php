<?php
require_once('FirePHP/fb.php');
ob_start();

// ex: http://localhost/CSD/page.php?page=input

$page=$_GET["page"];

//TODO maybe add some logging here

error_reporting( E_ALL ); // TODO remove before production
ini_set( 'display_errors', 1 ); // TODO remove before production
?>

<root>
	<navbar>
		<?php
			getfile('html/' . $page . '-navbar.html');
		?>
	</navbar>
	
	<content>
		<?php
			getfile('html/' . $page . '-content.html');
		?>
	</content>
	
	<modals>
		<?php
			getfile('html/' . $page . '-modals.html');
		?>
	</modals>
	
	<js>
		<?php
			getfile('script/' . $page . '.js');
		?>
	</js>
	
	<css>
		<?php
			getfile('css/' . $page . '.css');
		?>
	</css>
	
	
</root>

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

//make temp file

fb($page);

die($html);

?>