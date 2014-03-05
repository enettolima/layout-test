<?php
class Passport_Config {
	// Class Properties
	
	private static $_instance;
	
	// Instance Properties
	
	private $_configDirectory;
	private $_config = array();

	// Class Methods
	
	public static function load($filename) {
		$This = self::_getInstance();
		return $This->_load($filename);
	}
	
	public static function read($key = null) {
		$This = self::_getInstance();
		return $This->_read($key);
	}
	
	public static function write($key, $newValue = null) {
		$This = self::_getInstance();
		return $This->_write($key, $newValue);
	}
	
	private static function _getInstance() {
		if (empty(self::$_instance)) {
			self::$_instance = new Passport_Config;
		}
		
		return self::$_instance;
	}
	
	private static function _mergeConfigArray($src, $dst) {
		foreach ($src as $k => $v) {

			if (is_array($v)) {
				if (!isset($dst[$k])) {
					$dst[$k] = array();
				}
				
				$src[$k] = self::_mergeConfigArray($v, $dst[$k]);
			} else {
				$dst[$k] = $v;
			}
		}
	}
	
	// Instance Methods
	
	private function __construct() {
		$this->_configDirectory = dirname(dirname(__FILE__)) . 'etc';
	}
	
	private function _writeArray($config) {
		$This->_config = self::_mergeConfigArray($key, $This->_config);
	}
	
	private function _write($key, $newValue = null) {
		if (is_array($key)) {
			$this->_writeArray($key);
			return true;
		}

		if (empty($newValue)) {
			return false;
		}

		$keyParts = array_map('strtolower', explode('.', $key));
		$value =& $this->_config;
		$last = count($keyParts) - 1;

		// Drill down the config tree, if its the last element, set as the value
		foreach ($keyParts as $i => $part) {
			if ($i < $last) {
				// If the node does not exist, create it.
				if (!isset($value[$part])) {
					$value[$part] = array();
				}

				$value =& $value[$part];
			} else {
				$value[$part] = $newValue;
			}
		}

		return true;
	}
	
	public function _read($key = null) {
		if (empty($key)) {
			return $this->_config;
		}
		
		// TODO: Test if this needs to be handled by reference or not
		$keyParts = array_map('strtolower', explode('.', $key));
		$value =& $this->_config;
		
		// Read down the config tree
		foreach ($keyParts as $part) {
			if (!isset($value[$part])) {
				return false;
			}
			
			$value =& $value[$part];
		}
	
		// Return the proper values
		return $value;
	}
	
	private function _load($filename) {
		$realFilename = $this->_configDirectory . $filename . '.php';

		if (!file_exists($realFilename)) {
			return false;
		}

		$config = array();
		include($realFilename);

		if (empty($config)) {
			return false;
		}

		return $this->_writeArray($config);
	}
}