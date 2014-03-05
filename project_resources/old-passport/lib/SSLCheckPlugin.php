<?php
class SSLCheckPlugin extends Zend_Controller_Plugin_Abstract{
	public function preDispatch(Zend_Controller_Request_Abstract $request) {
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
		$fc = Zend_Controller_Front::getInstance();
		
		$controllerClass = ucwords($request->controller) . 'Controller';
		$controllerFile = null;
		
		//Find the controller file
		$test_file = $fc->getControllerDirectory($request->module) . '/' . $controllerClass . '.php';
		if (file_exists($test_file)) {
			$controllerFile = $test_file;
		}else
			return false;
		
		if ($request->module != $fc->getDefaultModule())
			$controllerClass = $request->module . "_" . $controllerClass;
			
		require_once($controllerFile);
		require_once('ISecureController.php');
		$reflect = new ReflectionClass($controllerClass);
		
		if ($reflect->isSubclassOf('ISecureController') && !$request->isSecure()) {
			$url = 
				'https://' . 
				$request->getHttpHost() .  
				$request->getRequestUri();
				
			$redirector->gotoUrlAndExit($url);
		} else if (!$reflect->isSubclassOf('ISecureController') && $request->isSecure()) {
			$url = 
				'http://' . 
				$request->getHttpHost() .  
				$request->getRequestUri();
				
			$redirector->gotoUrlAndExit($url);
		}
	}
}