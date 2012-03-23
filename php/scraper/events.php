<?php

	//get file to find number of teams
	$year = 2012
	//$contents = file_get_contents("https://my.usfirst.org/myarea/index.lasso?page=searchresults&omit_searchform=1&programs=FRC&season_FRC=".$year."&reports=events&results_size=1000000000", false);
	$contents = file_get_contents("event_testcase.html");

	//find out how many teams were returned (to determine # of pages)
	#preg_match('/All Areas\s+\((....|...|..|.) found,/', $contents, $matches);
	
	preg_match('/<tr bgcolor="#FFFFFF">.*</tr>', $contents, $matches);
	echo $matches;

?>
