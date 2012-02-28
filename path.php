<?php

require_once('FirePHP/fb.php');

$selfClosingTags = ['img','br'];

$html[] = [
	'html',
	[
		'head',
		[
			'title',
			'PATH Test'
		]
	],
	[
		'body',
		[
			'h1',
			'PATH is (P)HP (A)rrays (T)o (H)TML'
		],
		[
			'br'
		],
		[
			'p',
			'style' => 'color: red',
			'this is a test for PATH'
		]
	]
];

echo path($html[0]);

function path($array){
	global $selfClosingTags;

	if(isset($innerHTML)){
		echo "\n ---echo: " . $innerHTML;
	}

	//add stuff for self closing tags
	$tagName = $array[0];
	unset($array[0]);

	//make a way to manually specify a self closing tag
	$selfClosing = in_array($tagName, $selfClosingTags);

	$return = '<' . $tagName;

	if(!$selfClosing){//self closing tags can't have innerHTML
		$key = 1;
		$innerHTML = '';

		while (array_key_exists($key, $array)){
			if(is_array($array[$key])){
				$innerHTML .= path($array[$key]);
			} else {
				$innerHTML .= $array[$key];
			}
			unset($array[$key]);
			$key++;
		}
	}

	foreach($array as $key => $value){
		$return .= ' ' . $key . '="' . $value . '"';
	}

	//add stuff for self closing tags
	if(!$selfClosing){
		$return .= '>' . $innerHTML . '</' . $tagName . '>';
	} else {
		$return .= '/>';
	}

	return $return;
}

?>