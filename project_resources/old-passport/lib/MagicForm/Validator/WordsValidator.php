<?php
class WordsValidator {
	public function isValid($element) {
		return !preg_match('/[^A-Za-z0-9\' ]/', $element->value);
	}
}