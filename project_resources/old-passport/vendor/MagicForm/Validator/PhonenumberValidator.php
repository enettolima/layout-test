<?php
class PhonenumberValidator {
	public function isValid($element) {
		
		$numlen = 0;
		for ($i = 0; $i < strlen($element->value); $i++) {
			if (ctype_digit($element->value[$i]))
				$numlen++; 
		}
		
		return $numlen == 11 || $numlen == 10;
	}
}