<?php
/*
	this file contains functions needed to build/rebuild the directory structure and file structure (mostly for the tmp folder). Also it should set permissions for all directories.
*/

function clearTmp(){
	/* if not working:
	sudo chmod -R 775 /var/www/
	sudo chown -R sean:www-data /var/www/
	*/

	//TODO: fix file permissions below

	$cwd = getcwd();
	system("rm -rf " . $cwd . "/tmp");//remove current tmp

	mkdir($cwd . "/tmp");
	mkdir($cwd . "/tmp/pages");
	mkdir($cwd . "/tmp/backup");
	mkdir($cwd . "/tmp/css");
	mkdir($cwd . "/tmp/js");
	mkdir($cwd . "/tmp/db");
	mkdir($cwd . "/tmp/db/getTPIDs");
	mkdir($cwd . "/tmp/db/getTeamProfiles");
	mkdir($cwd . "/tmp/db/getEvents");
	mkdir($cwd . "/tmp/db/updateFMS");
	mkdir($cwd . "/tmp/db/export");
}

//TODO: add functions for export/import of data into JSON files
?>