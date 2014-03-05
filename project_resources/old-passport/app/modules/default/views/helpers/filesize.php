<?php
class Zend_View_Helper_FileSize {
	public function fileSize($size) {
		$suffixes = array('GB', 'MB', 'KB', 'B');
		foreach ($suffixes as $i => $suffix) {
			$n = pow(1024, count($suffixes) - $i - 1);
			if (intval($size) >= $n)
				return number_format($size / $n, 0) . ' ' . $suffix;
		}
		
		return false;
	}
}