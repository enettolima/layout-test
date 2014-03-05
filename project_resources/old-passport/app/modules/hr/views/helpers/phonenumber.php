<?php
class Hr_View_Helper_PhoneNumber {
	public function phoneNumber($str, $chr = '-') {
		$str = trim($str);
		if (!is_numeric($str) || strlen($str) > 11 || strlen($str) < 10)
			return $str;
			
		$parts = array();
		foreach(array(4, 3, 3, 1) as $partLen) {
			if (strlen($str) >= $partLen) {
				$parts[] = substr($str, -$partLen);
				$str = substr($str, 0, -$partLen);
			}
		}
		
		return implode($chr, array_reverse($parts));
	}
}