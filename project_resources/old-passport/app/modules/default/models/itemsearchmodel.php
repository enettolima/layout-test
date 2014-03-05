<?php
require_once('Zend/Search/Lucene.php');

class ItemSearchModel {
	var $index;
	var $luceneRoot;
	
	public function __construct()
	{	
		$this->luceneRoot = dirname(getcwd()) . "/data/lucene/";
	}
	
	function getIndexStatus()
	{
		$this->setupLucene();
		
		$statusFile = $this->luceneRoot . "create_index.log";
		if (file_exists($statusFile)) {
			$fd = fopen($statusFile, "r");
			$data = explode(' ', trim(fgets($fd)));
			
			return array(
				'sql_stat' => (int)$data[0],
				'index_stat' => (int)$data[1],
				'progress' => (int)$data[2],
				'building' => true);
		} else {
			return array(
				'size' => $this->index->count(),
				'count' => $this->index->numDocs(),
				'building' => false);
		}
	}
	
	function setupLucene() {
		$this->index = Zend_Search_Lucene::open($this->luceneRoot . 'test_index');

		Zend_Search_Lucene_Analysis_Analyzer::setDefault(
			new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive()
		);
	}
	
	function runIndexBuilder() {
		if (!file_exists($this->luceneRoot . "create_index.log"))
		{
			$cmd = "cmd /C php \"{$this->luceneRoot}create_index.php\"";
			
			$WshShell = new COM("WScript.Shell");
			$oExec = $WshShell->Run($cmd, 0, false);
			
			return true;
		}
		
		return false;
	}
	
	function getItemsBySearch($queryString, $page, $pageSize) {
		$this->setupLucene();
		
		try {
			Zend_Search_Lucene::setResultSetLimit(200);
			$hits = $this->index->find($queryString);
			
			$ret = array();
			foreach ($hits as $hit) {
				$doc = $hit->getDocument();	
				
				$row = array(
					'image' => $doc->image,
					'ITEM_NO' => $doc->item_no,
					'DESCRIPTION' => $doc->description,
					'D_NAME' => $doc->dname,
					'CASE_QTY' => $doc->caseqty);
			
				if ($doc->hasField('cname'))
					$row['C_NAME'] = $doc->cname;
					
				if ($doc->hasField('sname'))
					$row['S_NAME'] = $doc->sname;
					
				if ($doc->hasField('retail'))
					$row['RETAIL_PRICE'] = $doc->retail;
					
				if ($doc->hasField('franchise'))
					$row['FRANCHISE_PRICE'] = $doc->franchise;
					
				if ($doc->hasField('discount'))
					$row['DISCOUNT'] = $doc->discount;
					
				if ($doc->hasField('alu'))
					$row['ALU'] = $doc->alu;
					
				$objectRow = new stdClass();
		        foreach ($row as $akey => $aval)
		            $objectRow -> {$akey} = $aval;
		            
		        $ret[] = $objectRow;
			}
						
			if (count($ret) < $pageSize && $page == 0)
				return $ret;
			else
				return array_slice($ret, $page * $pageSize, $pageSize);
		} catch (Exception $e) {
			PassportLog::getInstance()->dump($e);
			return false;
		}
	}
}
?>