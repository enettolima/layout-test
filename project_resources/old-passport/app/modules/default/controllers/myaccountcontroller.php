<?php
require_once('MagicForm.php');
require_once(APP_ROOT . '/models/User.php');

class MyAccountController extends Zend_Controller_Action {
	function indexAction() {
		$user_id = Zend_Auth::getInstance()->getIdentity()->id;
		
		if (!isset($user_id)) {
			$this->_redirect('/');
			return;
		}
		
		if (($user = User::find($user_id)) === false) {
			$this->_redirect('/');
			return;
		}

		$passForm = new MagicForm('password_form');
		
		if ($this->_request->isPost()) {
			if (isset($_POST['change_password'])) {
				$success = false;
				
				try {
					$valid = $passForm->isValid();
				} catch (MagicFormException $e) {
					$valid = false;
				}
				
				if ($valid) {
					$user->passhash = sha1($_POST['password']);
					
					if ($user->trySave()) {
						$success = true;
					}
				}
				
				if ($success) {
					$this->view->message = 'Password changed!';
				} else {
					$this->view->message = 'Unable to change password.';
				}
				
				$this->view->success = $success;
			}
		}
		
		if (!headers_sent()) {
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
		}
		
		$this->view->user = $user;
	}
}