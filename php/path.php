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
	$isSelfClosing = in_array($tagName, $selfClosingTags);

	$return = '<' . $tagName;

	if(!$isSelfClosing){//self closing tags can't have innerHTML
		$key = 1;
		$innerHTML = '';

		while (array_key_exists($key, $array)){
			if(is_array($array[$key])){
				$innerHTML .= path($array[$key]);//recursivly call path to process nested tags
			} else {
				$innerHTML .= $array[$key];
			}
			unset($array[$key]);
			$key++;
		}
	}

	//process attributes
	foreach($array as $key => $value){
		//encode any double quotes from string (these can't be in attributes)...this is important for attributes which contain script
		$value = preg_replace('/\"/', '&quot;', $value);
		$return .= ' ' . $key . '="' . $value . '"';
	}

	
	if(!$isSelfClosing){
		$return .= '>' . $innerHTML . '</' . $tagName . '> ';//add stuff for regular tags
	} else {
		$return .= '/> ';//add stuff for self closing tags
	}
	//logger("Generated PATH path for ".$logName);
	return $return;
}
?>
