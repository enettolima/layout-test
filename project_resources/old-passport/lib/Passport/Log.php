<?php
Zend_Loader::loadClass('Zend_Log');

class Passport_Log extends Zend_Log {
	private static $instance;
	private $logRoot;
	
	public function __construct() {
		$this->logRoot = dirname(getcwd()) . "/logs/";
		$filename = $this->logRoot . "passport.log";
		
		if (!file_exists($filename)) {
			$fd = fopen($filename, 'w');
			fclose($fd);
		}
		
		$writer = new Zend_Log_Writer_Stream($this->logRoot . "passport.log");
		parent::__construct($writer);
	}
	
	public static function getInstance() {
		if (!is_object(self::$instance)) {
			self::$instance = new Passport_Log;
		}
		
		return self::$instance;				
	}
	
	public static function log($msg, $level = Zend_Log::INFO) {
		$This = self::getInstance();
		return $this->log($msg, $level);
	}
	
	public function log($msg, $level = Zend_Log::INFO) {
		if (is_array($msg) || is_object($msg)) {
			$msg = print_r($msg, true);
		}
		
		parent::log($msg, $level);
	}
}