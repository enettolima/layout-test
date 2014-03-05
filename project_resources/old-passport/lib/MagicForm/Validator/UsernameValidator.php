<?php
class UsernameValidator {
	public function isValid($element) {
		return !preg_match('/[^A-Za-z0-9.@_-]/', $element->value);
	}
}