<?php
Zend_Loader::loadClass('Zend_Log');
class PassportLog extends Zend_Log
{
	private static $instance;
	private $logRoot;
	
	public function __construct()
	{
		
		
		$this->logRoot = dirname(getcwd()) . "/logs/";
		$filename = $this->logRoot . "passport.log";
		if (!file_exists($filename)) {
			$fd = fopen($filename, 'w');
			fclose($fd);
		}
		
		$writer = new Zend_Log_Writer_Stream($this->logRoot . "passport.log");
		parent::__construct($writer);
	}
	
	public static function getInstance()
	{
		if (!is_object(PassportLog::$instance))
			PassportLog::$instance = new PassportLog();
			
		return PassportLog::$instance;				
	}
	
	public function quickLog($msg)
	{
		$this->log($msg, Zend_Log::INFO);		
	}
	
	public function dump($var) {
		ob_start();
		var_dump($var);
		$data = ob_get_contents();
		ob_end_clean();
		
		$this->quickLog($data);
	}
}
?>