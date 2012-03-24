<?php //PATH is (P)HP (A)rrays (T)o (H)TML

//TODO: add in import tag (gets & adds file in place of tag)
//TODO: add in body#idOfTag and body.className.anotherClass shorthand syntax
//TODO: get selector library like sizzle for php


$selfClosingTags = ['img', 'br', 'input'];

function path($array, $logName="unknown"){
	global $selfClosingTags;

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
		$return .= '>' . $innerHTML . '</' . $tagName . '> ';
	} else {
		$return .= '/> ';
	}
	logger("Generated PATH path for ".$logName);
	return $return;
}
?>