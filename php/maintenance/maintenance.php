<?php

function clearTmp(){
	/* if not working:
	sudo chmod -R 775 /var/www/
	sudo chown -R sean:www-data /var/www/
	*/

	//TODO: fix file permissions below

	$cwd = getcwd();
	system("rm -rf " . $cwd . "/tmp");
	mkdir($cwd . "/tmp/pages", 0777, true);
	mkdir($cwd . "/tmp/backup");
	mkdir($cwd . "/tmp/db/getTPIDs", 0777, true);
	mkdir($cwd . "/tmp/db/getTeamProfiles");
	mkdir($cwd . "/tmp/db/getEvents");
	mkdir($cwd . "/tmp/db/updateFMS");
	mkdir($cwd . "/tmp/db/export");
}

function doDbExport() {
	$this->db = xn("db");
	
	$db = $this->_mongo->selectDB($this->db);
	$this->collections = MDb::listCollections($db);
	$this->selectedCollections = array();
	if (!$this->isPost()) {
		$this->selectedCollections[] = xn("collection");
	}
	else {
		$checkeds = xn("checked");
		$canDownload = xn("can_download");
		if (is_array($checkeds)) {
			$this->selectedCollections = array_keys($checkeds);
		}
		
		sort($this->selectedCollections);
		
		import("classes.VarExportor");
		$this->contents =  "";
		$this->countRows = 0;
		
		//indexes
		foreach ($this->selectedCollections as $collection) {
			$collObj = $db->selectCollection($collection);
			$infos = $collObj->getIndexInfo();
			foreach ($infos as $info) {
				$options = array();
				if (isset($info["unique"])) {
					$options["unique"] = $info["unique"];
				}
				$exportor = new VarExportor($db, $info["key"]);
				$exportor2 = new VarExportor($db, $options);
				$this->contents .= "\n/** {$collection} indexes **/\ndb.getCollection(\"" . addslashes($collection) . "\").ensureIndex(" . $exportor->export(MONGO_EXPORT_JSON) . "," . $exportor2->export(MONGO_EXPORT_JSON) . ");\n";
			}
		}
		
		//data
		foreach ($this->selectedCollections as $collection) {
			$cursor = $db->selectCollection($collection)->find();
			$this->contents .= "\n/** " . $collection  . " records **/\n";
			foreach ($cursor as $one) {
				$this->countRows ++;
				$exportor = new VarExportor($db, $one);
				$this->contents .= "db.getCollection(\"" . addslashes($collection) . "\").insert(" . $exportor->export(MONGO_EXPORT_JSON) . ");\n";
				unset($exportor);
			}
			unset($cursor);
		}
		
		if (x("can_download")) {
			$prefix = "rockmongo-export-" . urlencode($this->db) . "-" . time();
			
			//gzip
			if (x("gzip")) {
				ob_end_clean();
				header("Content-type: application/x-gzip");
				header("Content-Disposition: attachment; filename=\"{$prefix}.gz\")"); 
				echo gzcompress($this->contents, 9);
				exit();
			}
			else {
				ob_end_clean();
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=\"{$prefix}.js\")");
				echo $this->contents;
				exit();
			}
		}
	}
}
	

function import($file){
	//this function is used for importing backups back into the db, but does not validate the data

	if (!empty($_FILES["json"]["tmp_name"])) {
		$tmp = $_FILES["json"]["tmp_name"];
		
		//read file by it's format
		$body = "";
		if (preg_match("/\.gz$/", $_FILES["json"]["name"])) {
			$body = gzuncompress(file_get_contents($tmp));
		}
		else {
			$body = file_get_contents($tmp);
		}
		
		$ret = $db->execute('function (){ ' . $body . ' }');
		logger($file . ' is imported');
	}
	else {
		$this->error = "Either no file input or file is too large to upload.";
	}
}	

?>