<?php
define('MFCACHE_PATH', 'c:/web/tmp/forms');
define('MFVALIDATOR_PATH', dirname(__FILE__) . '/MagicForm/Validator');

class MagicFormException extends Exception { }

class MagicForm {
	private $_isValid;
	
	private $id;
	private static $results = array();
	
	public function __construct($id) {
		$this->id = $id;
		MagicForm::$results[$id] = array();
		
		$this->_settings = (object)array(
			'exceptions' => true);
	}
	
	public function reset() {
		MagicForm::$results[$this->id] = array();
	}
	
	public function isValid() {
		if (empty($_POST))
			return false;
	
		if (isset($this->_isValid))
			return $this->_isValid;
			
		// Load the rules.
		try {
			// Get the form key.
			if (!isset($_POST['form_key']))
				throw new MagicFormException("No form key submitted.");
			else
				$key = $_POST['form_key'];
			
			// Load the file
			$formPath = MFCACHE_PATH . "/{$key}.form";
			if (!file_exists($formPath))
				throw new MagicFormException("No stored form matching submitted key.");
			else
				$stored = unserialize(file_get_contents($formPath));
			
			// Check IP matches
			if (!isset($stored->ip) || $stored->ip != $_SERVER['REMOTE_ADDR'])
				throw new MagicFormException("Stored form IP does not match submitter IP.");
			
			// Check form id matches
			if (!isset($stored->id) || $stored->id != $this->id)
				throw new MagicFormException("Stored form id does not match stored id.");
			
			// Check integrity of the file
			if (!is_array($stored->elements))
				throw new MagicFormException("Stored form is invalid, no elements found.");
				
			$this->_results = array();
			$this->_isValid = true;
			
			// Check the post data with the rules
			foreach($stored->elements as $name => $element) {
				MagicForm::$results[$this->id][$name] = array();
				
				$element->name = $name;
				
				if (isset($_POST[$name])) 
					$element->value = $_POST[$name];
				
				// Decide if the value is present or not
				$present = false;
					
				// TODO: Add all possible element types.
				if ($element->type == 'checkbox' || $element->type == 'radio') {
					$present = isset($element->value);
				} else if ($element->type == 'text' || $element->type == 'password' || $element->type == 'hidden') {
					$present = isset($element->value) && strlen($element->value) > 0;
				}
				
				// Check required, including syntax for conditional requirement
				if (isset($element->required)) {
					$required = false;
				
					if ($element->required == 'true' || $element->required == '1' || $element->required == 'yes' || $element->required == 'required') {
						$required = true;
					} else if ($element->required == 'false' || $element->required == '0' || $element->required == 'no' || $element->required == 'notrequired') {
						$required = false;
					// Dynamic requirement
					} else if (preg_match('/(\w+)\s*(=|>|<|<>)\s*(\w+)/', $element->required, $matches)) {
						list($expression, $dependant, $op, $value) = $matches;

						$required = isset($_POST[$dependant]) && compare($_POST[$dependant], $op, $value);
					} else {
						$required = isset($_POST[$dependant]);
					}
				
					MagicForm::$results[$this->id][$name]['required'] = ($required && $present) || !$required;
					
					if (!MagicForm::$results[$this->id][$name]['required'])
						$this->_isValid = false;
				}
				
				// Check rules
				if ($present && is_array($element->validators)) {
					foreach ($element->validators as $command => $arguments) {
						$validatorClass = ucwords($command) . "Validator";
						$validatorPath = MFVALIDATOR_PATH . "/{$validatorClass}.php";
						
						if (!file_exists($validatorPath))
							continue; // Fail silently
						
						require_once($validatorPath);
						
						$validator = new $validatorClass;
						array_unshift($arguments, $element);
						MagicForm::$results[$this->id][$name][$command] = call_user_func_array(array($validator, 'isValid'), $arguments);
						
						if (!MagicForm::$results[$this->id][$name][$command])
							$this->_isValid = false;
					}
				}
			}
		} catch (MagicFormException $e) {
			$this->_isValid = false;
			
			if ($this->_settings->exceptions === true)
				throw $e;
		}
		
		return $this->_isValid;
	}
	
	public static function wasUsed() {
		return isset(MagicForm::$results) && count(MagicForm::$results) > 0;
	}
	
	public static function wand($data) {
		if (!MagicForm::wasUsed())
			return;
		
		// Open output to transform.
		$d = new DOMDocument;
		@$d->loadHTML($data);
		$x = new DOMXPath($d);

		foreach (MagicForm::$results as $id => $results) {
			// Generate new form key.
			do {
				$newKey = uniqid();
			} while (file_exists(MFCACHE_PATH . "/{$newKey}.form"));
		
			$form = $d->getElementById($id);
			if (empty($form)) {
				throw new MagicFormException("Form {$id} not found.");
			}
			
			// Create form key element.
			$keyElement = $d->createElement('input');
			$keyElement->setAttribute('type', 'hidden');
			$keyElement->setAttribute('name', 'form_key');
			$keyElement->setAttribute('value', $newKey);
			
			$form->appendChild($keyElement);

			$storage = array();
			
			// Read form rules.
			$options = array();
			$elements = $x->query(".//*[@validate][@name] | .//*[@required][@name]", $form);
			foreach ($elements as $element) {			
				$realname = strtolower($element->getAttribute('name'));
				
				if (($r = strpos($realname, '[')) !== false && strpos($realname, ']') !== false)
					$name = substr($realname, 0, $r);
				else
					$name = $realname;
					
				$tag = strtolower($element->tagName);
				
				if ($tag == 'input')
					$type = strtolower($element->getAttribute('type'));
				else if ($tag == 'select')
					$type = 'select';
				else if ($tag == 'textarea')
					$type = 'textarea';
				else
					continue;
				
				if (!isset($storage[$name])) {
					$storage[$name] = (object)array('required' => false, 'validators' => array(), 'type' => $type);
					
					if ($element->hasAttribute('validate')) {
						$validators = array();
						
						foreach(array_map('trim', explode(';', strtolower($element->getAttribute('validate')))) as $validator) {
							$arguments = preg_split('/\s+/', $validator);
							$command = array_shift($arguments);
							$validators[$command] = $arguments;
						}
						
						if ($type == 'select' && isset($validators['strict'])) {
							unset($validators['strict']);
							
							$options[$name] = array();
							foreach($x->query('.//option', $form) as $optElement) {
								// TODO: Support no value attribute
								if ($optElement->hasAttribute('value'))
									$options[$name][] = $optElement->getAttribute('value');
							}
						}
						
						$storage[$name]->validators = $validators;
					}
					
					if ($element->hasAttribute('required')) {
						$storage[$name]->required = strtolower(trim($element->getAttribute('required')));
					}
				}
					
				if ($element->hasAttribute('value'))
					MagicForm::nestValue($realname, $element->getAttribute('value'), $options);
			}
			
			foreach ($options as $name => $option) {
				if (is_array($option) && count($option) > 0)
					$storage[$name]->options = $option;
			}
			
			// Save form rules and element data.
			$formPath = MFCACHE_PATH . "/{$newKey}.form";
			file_put_contents($formPath, serialize((object)array(
				'ip' => $_SERVER['REMOTE_ADDR'],
				'id' => $id,
				'elements' => $storage)));

			// Delete the old file if it exists.
			if (isset($_POST['form_key']) && ctype_alnum($_POST['form_key'])) {
				$oldFile = MFCACHE_PATH . "/{$_POST['form_key']}.form";
				
				if (file_exists($oldFile))
					unlink($oldFile);
			}
			
			// Transform errors based on results.
			foreach ($x->query('.//error[@for]', $form) as $element) {
				$for = $element->getAttribute('for');
				$errorElement = $element->parentNode->insertBefore(new DOMElement('label'), $element);
				$classes = array('error');
				
				if ($element->hasAttribute('class'))
					$classes[] = $element->getAttribute('class');
		
				if (!isset(MagicForm::$results[$id][$for])) {
					$classes[] = 'validatestate_none';
				} else if ($element->hasAttribute('validator')) {
					if (isset(MagicForm::$results[$id][$for][$element->getAttribute('validator')]))
						$classes[] = 'validatestate_' . (MagicForm::$results[$id][$for][$element->getAttribute('validator')] ? 'valid' : 'invalid');
					else
						$classes[] = 'validatestate_none';
				} else {
					$valid = true;
					
					foreach (MagicForm::$results[$id][$for] as $isValid) {
						if (!$isValid) {
							$valid = false;
							break;
						}
					}
				
					$classes[] = 'validatestate_' . ($valid ? 'valid' : 'invalid');
				}
				
				// Pass through select attributes
				foreach (array('style', 'validator') as $key) {
					if ($element->hasAttribute($key)) {
						$errorElement->setAttribute($key, $element->getAttribute($key));
					}
				}
					
				// Pass through transformed attributes
				$errorElement->setAttribute('class', implode(' ', $classes));
				$errorElement->setAttribute('for', $for);

				// Copy children to new parent
				foreach ($element->childNodes as $child)
					$errorElement->appendChild($child);
					
				// Delete old parent
				$element->parentNode->removeChild($element);
			}
		}
		
		// Output new markup
		return $d->saveHTML();
	}
	
	private static function nestValue($name, $value, &$array) {
		$l = strpos($name, '[');
		$r = strpos($name, ']');
		
		if ($l === false || $r === false || $r < $l) {
			$array[$name] = $value;
			
			return;
		}
		
		$new_name = substr($name, 0, $l);
		
		if (!isset($array[$new_name]))
				$array[$new_name] = array();
				
		if ($r - $l == 1) { 
			if (count($array[$new_name]) > 0)
				$key = max(array_keys($array[$new_name])) + 1;
			else
				$key = 0;
		} else { 
			$key = substr($name, $l + 1, $r - $l - 1);
			
			if ($r + 1 < strlen($name))
				$key .= substr($name, $r + 1);
		}
		
		MagicForm::nestValue($key, $value, $array[$new_name]);
	}
}


function compare($l, $op, $r) {
	if (!isset($l) || !isset($op) || !isset($r))
		return false;

	switch ($op) {
		case '=' : return $l == $r;
		case '<>' : return $l != $r;
		case '>' : return $l > $r;
		case '<' : return $l < $r;
		case '>=' : return $l >= $r;
		case '<=' : return $l <= $r;
	}

	return false;
}