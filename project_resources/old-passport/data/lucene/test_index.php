<?php
	set_include_path("c:/web/passportdev/lib" . PATH_SEPARATOR . get_include_path());
	error_reporting(E_ALL);
	
	require_once('Zend/Loader.php');
	Zend_Loader::registerAutoload();

	echo "Opening index...";
	$index = Zend_Search_Lucene::open('test_index');
	echo "[OK]\r\n";
	
	echo "Creating query...";
	$query = '+dname:"gifts" +cname:"lasercube"';
	echo "[OK]\r\n";

	echo "Searching index...";
	$hits = $index->find($query);
	echo "[OK]\r\n";
	
	foreach ($hits as $hit) {
		echo "ITEM: " . $hit->item_no . "\r\n";
		echo "\tD_NAME: " . $hit->dname . "\r\n";
		echo "\tC_NAME: " . $hit->cname . "\r\n";
		echo "\tS_NAME: " . $hit->sname . "\r\n";
	}
	
	echo "DONE\r\n";
?>