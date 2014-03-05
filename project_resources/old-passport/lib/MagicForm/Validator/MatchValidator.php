<?php
class MatchValidator {
	public function isValid($element, $match) {
		return isset($_POST[$match]) && $element->value == $_POST[$match];
	}
}