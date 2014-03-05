<?php
class StrlenValidator  {
	public function isValid($element, $max, $min = false) {
		if ($min) {
			if (strlen($element->value) > $max || strlen($element->value) < $min)
				return false;
		} else {
			if (strlen($element->value) != $max)
				return false;
		}
		
		return true;
	}
}