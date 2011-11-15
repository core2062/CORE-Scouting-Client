<?php
	 // TODO remove this section before production
	require_once('FirePHP/fb.php');
	ob_start();
	
	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );
?>

<?php
// ex: http://localhost/CSD/page.php?pages={"0":"input", "1":"home"}

// TODO allow multiple pages to be sent per request

$embedded = json_decode($_GET["pages"], true);
$length = count($embedded);

//TODO maybe add some logging here

error_reporting( E_ALL ); // TODO remove before production
ini_set( 'display_errors', 1 ); // TODO remove before production
?>

<root>
	<navbar>
		<?php
			echo embed('html/', '-navbar.html');
		?>
	</navbar>
	
	<content>
		<?php
			echo embed('html/', '-content.html');
		?>
	</content>
	
	<sidebar>
		<?php
			echo embed('html/', '-sidebar.html');
		?>
	</sidebar>
	
	<modals>
		<?php
			echo embed('html/', '-modals.html');
		?>
	</modals>
	
	<js>
		<?php
			echo embed('script/', '.js');
		?>
	</js>
	
	<css>
		<?php
			echo embed('css/', '.css');
		?>
	</css>
	
	
</root>

<?php


function embed($folder, $extension) {
	global $length;
	global $embedded;
	$output = ""; //start output var
	
	for ($i = 0; $i < $length; ++$i) {
		$file = $folder . $embedded[$i] . $extension;
		
		if (file_exists($file) == true) {
			$output .= file_get_contents($file);
		}
	}
	return $output;
}

$html = ob_get_contents();
ob_clean (); //empty output buffer


$html = preg_replace('/<!--(.|\s)*?-->/', '', $html); //removes comments
$html = preg_replace('/\s+/', ' ',$html); //removes double spaces, indents, and line breaks
$html = preg_replace('/\> </', '><',$html); // removes spaces between tags

//optimize & embed css and js (must be after html processing)
//base64 images ?

//make temp file

die($html);

?>