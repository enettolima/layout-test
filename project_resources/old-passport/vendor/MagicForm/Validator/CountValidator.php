<?php
class CountValidator {
	public function isValid($element, $min, $max = false) {
		if (!is_array($element->value))
			return false;
			
		$count = count($element->value);
	
		return ($max ? (int)$max >= $count && (int)$min <= $count : (int)$min <= $count);
	}
}