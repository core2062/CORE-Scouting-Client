<?php
 // remove this section before production
require_once('FirePHP/fb.php');
ob_start();

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
?>

<?php
// ex: http://localhost/CSD/page.php?pages={"0":"input", "1":"home"}
//TODO make sure this page's result gets cached by the browser

$embedded = json_decode($_GET["pages"], true);

sort($embedded); //make sure that filename being searched for in cache is same, regardless of request order

//TODO maybe add some logging here

$length = count($embedded);

$filename = $embedded[0];
for ($i = 1; $i < $length; ++$i) {
	$filename .= "," . $embedded[$i];
}
$filename = 'cache/' . $filename . '-page';

$htmlparts[0] = 'navbar';
$htmlparts[1] = 'content';
$htmlparts[2] = 'sidebar';
$htmlparts[3] = 'modals';

$parts_length = count($htmlparts);

if (file_exists($filename) == true){

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
		$json = file_get_contents ($filename);
		fb('cached');
		ob_clean (); //empty output buffer
		die($json);
	}
}

//compile needed files into JSON
function htmlembed($type) {
	global $length;
	global $embedded;
	$return = "";
	
	for ($i = 0; $i < $length; ++$i) {
		$file = 'html/' . $embedded[$i] . '-' . $type . '.html';
		
		if (file_exists($file) == true) {
			$return .= file_get_contents($file);
		}
	}
	
	$return = preg_replace('/<!--(.|\s)*?-->/', '', $return); //removes comments
	$return = preg_replace('/\s+/', ' ',$return); //removes double spaces, indents, and line breaks
	$return = preg_replace('/\s</', '<',$return); // removes spaces between tags
	$return = trim($return);
	$return = json_encode($return);
	return $return;
}

function jsembed() {
	include 'php/jsminplus.php';
	global $length;
	global $embedded;
	$return = "";
	
	for ($i = 0; $i < $length; ++$i) {
		$file = "script/" . $embedded[$i] . ".js";
		
		if (file_exists($file) == true) {
			$return .= file_get_contents($file);
		}
	}
	
	$return = JSMinPlus::minify($return);
	$return = trim($return);
	$return = json_encode($return);
	return $return;
}

function cssembed() {
	global $length;
	global $embedded;
	$return = "";
	
	for ($i = 0; $i < $length; ++$i) {
		$file = "css/" . $embedded[$i] . ".css";
		
		if (file_exists($file) == true) {
			$return .= file_get_contents($file);
		}
	}
	
	$return = trim($return);
	$return = json_encode($return);
	return $return;
	//TODO add in csstidy + fix gradient support
	//base64 images ?
}

//put complied JSON in vars
for ($i = 0; $i < $parts_length; ++$i) {
	eval ('$' . $htmlparts[$i] . "= htmlembed('" . $htmlparts[$i] . "');");
}
$js = jsembed();
$css = cssembed();

//use vars to form JSON string
$json = '{"content":[{"navbar":' . $navbar . '},{"content":' . $content . '},{"sidebar":' . $sidebar . '},{"modals":' . $modals . '},{"js":' . $js . '}{"css":' . $css . '}]}';

//cache data to temporary file
$fp = fopen($filename, "w+"); 
fwrite($fp, $json); 
fclose($fp); 

ob_clean (); //empty output buffer
die($json);
?>