<?php
class Passport_Acl {
	private static $instance;
	private $rules;
	
	public function __construct(User $user) {
		$rules = RoleAccessRule::findByRole($user->role_id)->toArray();

		foreach ($rules as &$rule) {
			$rule['allow'] = $rule['allow'] == 'Y';
			unset($rule['id']);
			unset($rule['role_id']);
		}
		
		$this->rules = $rules;
	}
	
	public function isAllowed($module, $controller, $action) {
		$allowed = false;
		
		foreach ($this->rules as $rule) {
			$match = 
				($module == $rule['module'] || is_null($rule['module'])) &&
				($controller == $rule['controller'] || is_null($rule['controller'])) &&
				($action == $rule['action'] || is_null($rule['action']));
			
			if ($match) {
				$allowed = $rule['allow'];
			}
		}
		
		return $allowed;
	}
	
	public static function getInstance(User $user) {
		// Singleton that works across sessions
		if (!isset(Passport_Acl::$instance)) {
			$s = new Zend_Session_Namespace('passport');
			
			if (!isset($s->acl)) {
				$s->acl = new Passport_Acl($user);
			}
			
			self::$instance = $s->acl;
		}
		
		return self::$instance;
	}
}