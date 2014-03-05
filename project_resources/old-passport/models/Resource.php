<?php
class Resource {
	private static $filename = APP_ROOT . '/data/resources.dat';
	private static $all;
	private $values;
	
	public function __construct() {
		$this->values = array();
	}
	
	public function __set($k, $v) {
		$this->values[$k] = $v;
	}
	
	public function __get($k) {
		return $this->values[$k];
	}
	
	public static function findAll() {
		if (!isset(Resource::$all)) {
			Resource::$all = unserialize(file_get_contents(Resource::$filename));
		}
		
		return Resource::$all;
	}
	
	public static function save() {
		file_put_contents(Resource::$filename, serialize(Resource::$all));
	}
}