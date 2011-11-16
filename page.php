<?php
	 // TODO remove this section before production
	require_once('FirePHP/fb.php');
	ob_start();
	
	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );
?>

<?php
// ex: http://localhost/CSD/page.php?pages={"0":"input", "1":"home"}

$embedded = json_decode($_GET["pages"], true);
$length = count($embedded);

//TODO maybe add some logging here

?>

<root>
	<navbar>
		<?php
			embed('html/', '-navbar.html');
		?>
	</navbar>
	
	<content>
		<?php
			embed('html/', '-content.html');
		?>
	</content>
	
	<sidebar>
		<?php
			embed('html/', '-sidebar.html');
		?>
	</sidebar>
	
	<modals>
		<?php
			embed('html/', '-modals.html');
		?>
	</modals>
	
	<js></js>
	
	<css>
		<?php
			embed('css/', '.css');
			//TODO add in csstidy + fix gradient support
		?>
	</css>
	
	
</root>

<?php

//TODO fix up this area using the code from index.php, and change from XML to JSON!

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
	echo $output;
}

$html = ob_get_contents();
ob_clean (); //empty output buffer

$html = preg_replace('/<!--(.|\s)*?-->/', '', $html); //removes comments
$html = preg_replace('/\s+/', ' ',$html); //removes double spaces, indents, and line breaks
$html = preg_replace('/\/> </', '/><',$html); // removes spaces between tags

//optimize css and js
//base64 images ?

//make temp file

die($html);

?>