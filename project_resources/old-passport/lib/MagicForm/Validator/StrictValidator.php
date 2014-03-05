<?php
class StrictValidator {
	public function isValid($element) {
		return isset($element->options) && recursive_check($element->options, $element->value);
	}
}

function recursive_check($options, $values) {
	if (is_array($values)) {
		foreach ($values as $key => $value) {
			if (is_array($value)) {
				if (!isset($options[$key]) || !recursive_check($options[$key], $value))
					return false;
			} else if (!in_array($value, $options)) {
				return false;
			}
		}
		
		return true;
	} 

	return in_array($values, $options);
}