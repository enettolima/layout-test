<?php
class AlnumValidator {
	public function isValid($element) {
		return !preg_match('/\W/', $element->value);
	}
}