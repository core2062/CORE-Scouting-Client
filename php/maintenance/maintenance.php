<?php
/*
	this file contains functions needed to build/rebuild the directory structure and file structure (mostly for the tmp folder). Also it should set permissions for all directories.
*/

function clearTmp(){
	/* if not working:
	sudo chmod -R 775 /var/www/
	sudo chown -R slang:www-data /var/www/
	*/

	//TODO: fix file permissions below

	$cwd = getcwd();
	system("rm -rf " . $cwd . "/tmp");//remove current tmp

	mkdir($cwd . "/tmp");
	mkdir($cwd . "/tmp/pages");
	mkdir($cwd . "/tmp/css");
	mkdir($cwd . "/tmp/js");
}

?>