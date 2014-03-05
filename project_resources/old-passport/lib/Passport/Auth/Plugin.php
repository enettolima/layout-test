<?php
Zend_Loader::loadClass('Zend_Controller_Plugin_Abstract');

class Passport_Auth_Plugin extends Zend_Controller_Plugin_Abstract {	
	public function preDispatch(Zend_Controller_Request_Abstract $request) {
		$noAuth = array(
			'module' => 'default',
			'controller' => 'auth',
			'action' => 'login');
		
		$noAcl = array(
			'module' => 'default',
			'controller' => 'error',
			'action' => 'noacl');
		
		$controller = $request->controller;
		$action = $request->action;
		$module = $request->module;
		
		if ($module == 'default' && $controller == 'auth')
			return;
			
		if ($module == 'default' && $controller == 'error')
			return;
			
		$auth = Zend_Auth::getInstance();

		// Default role to guest
		if ($auth->hasIdentity()) {	
			if (($user = $auth->getIdentity()) instanceof User) {
				$user = $auth->getIdentity();
				$acl = Passport_Acl::getInstance($user);
				
				try {
					if (!$acl->isAllowed($module, $controller, $action)) {
						extract($noAcl);
					}
				} catch (Exception $e) {
					extract($noAcl);
				}
			} else {
				$auth->clearIdentity();
				extract($noAuth);
			}
		} else {
			extract($noAuth);
		}
		
		$request->setModuleName($module);
		$request->setControllerName($controller);
		$request->setActionName($action);
	}
}