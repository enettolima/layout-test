<?php
define('APP_ROOT', realpath(dirname(__FILE__) . "/../.."));

$LOG_FILE = APP_ROOT . '/data/lucene/create_index.log';

set_include_path(APP_ROOT . "/vendor" . PATH_SEPARATOR . get_include_path());

require_once('Zend/Loader.php');
Zend_Loader::registerAutoload();

$config = new Zend_Config_Ini(APP_ROOT . "/app/etc/oracle.ini", 'live');

$fd = fopen($LOG_FILE, 'w');
writeStatus($fd, 0, 0, 0);

$conn = oci_connect($config->database->user, $config->database->password, $config->database->tnsname);

$SQL_SELECT_ALL = "SELECT si.* FROM WebAvailableItems wi LEFT JOIN SimpleInventory si ON(si.item_no = wi.item_no)";
$SQL_SELECT_COUNT = "SELECT COUNT(item_no) FROM WebAvailableItems";

if (oci_error())
{
	writeStatus($fd, 2, 0, 0);
	exit();
}

$stmt = oci_parse($conn, $SQL_SELECT_COUNT);
oci_execute($stmt);
$countRow = oci_fetch_row($stmt);
$count = $countRow[0];
oci_free_statement($stmt);

if (oci_error())
{
	writeStatus($fd, 2, 0, 0);
	exit();
}

$stmt = oci_parse($conn, $SQL_SELECT_ALL);
oci_execute($stmt);

if (oci_error())
{
	writeStatus($fd, 2, 0, 0);
	exit();
}

$index = Zend_Search_Lucene::create(APP_ROOT . '/data/lucene/test_index');
if (!isset($index)) {
	writeStatus($fd, 1, 2, 0);
	exit();
}

echo "$count\r\n";
$done = $percent = 0;
while ($row = oci_fetch_assoc($stmt))
{	
	$doc = new Zend_Search_Lucene_Document();
	
	$doc->addField(Zend_Search_Lucene_Field::Keyword('item_no', $row['ITEM_NO']));
	
	if (isset($row['DESCRIPTION']))
		$doc->addField(Zend_Search_Lucene_Field::Text('description', $row['DESCRIPTION']));
	
	if (isset($row['D_NAME']))
		$doc->addField(Zend_Search_Lucene_Field::Text('dname', $row['D_NAME']));
		
	if (isset($row['C_NAME']))
		$doc->addField(Zend_Search_Lucene_Field::Text('cname', $row['C_NAME']));
		
	if (isset($row['S_NAME']))
		$doc->addField(Zend_Search_Lucene_Field::Text('sname', $row['S_NAME']));
		
	if (isset($row['ALU']))
		$doc->addField(Zend_Search_Lucene_Field::Keyword('alu', $row['ALU']));
		
	if (isset($row['HAS_IMAGE']))
		$doc->addField(Zend_Search_Lucene_Field::Keyword('image', $row['HAS_IMAGE']));
	
	if (isset($row['RETAIL_PRICE']))
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('retail', (float)$row['RETAIL_PRICE']));
		
	if (isset($row['FRANCHISE_PRICE']))
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('franchise', (float)$row['FRANCHISE_PRICE']));
		
	if (isset($row['MARKDOWN']))
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('discount', $row['MARKDOWN']));
		
	if (isset($row['CASE_QTY']))
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('caseqty', $row['CASE_QTY']));	
	
	$index->addDocument($doc);
	
	$new_percent = intval((++$done / $count) * 100) ;
	if ($new_percent > $percent)
	{
		$percent = $new_percent;
		writeStatus($fd, 1, 1, $percent);
	}
}
	
fclose($fd);
oci_close($conn);
$index->commit();
$index->optimize();

while(!unlink($LOG_FILE))
	sleep(1);

function writeStatus($fd, $sql, $index, $perc)
{
	rewind($fd);
	fputs($fd, "$sql $index $perc\r\n");
	fflush($fd);
}
