<?php
class AuthController extends Zend_Controller_Action {
	public function loginAction() {
		$this->check_ie6(); // Check for IE6 and redirect if needed.
		
		$auth = Zend_Auth::getInstance();
		
		$this->_helper->layout->disableLayout();
		$front = Zend_Controller_Front::getInstance();
        
		if ($this->_request->isPost()) {
			$this->view->redirect = $this->_request->getParam('redirect', '/');
			
			$f = new Zend_Filter_StripTags();
			$username = $f->filter($this->_request->getParam('username'));
			$passhash = $this->_request->getParam('passhash');
			if (!isset($passhash)) {
				$passhash = hash('sha1', $this->_request->getParam('password'));
			}
			
			$adapter = new Passport_Auth_Adapter($username, $passhash);
			$result = $auth->authenticate($adapter);
			if ($result->isValid()) {				
				$this->_redirect($this->view->redirect);
				return;	
			} else {
				$this->view->errors = $result->getMessages();
			}
		} else {
			$getFields = array();
			foreach ($_GET as $k => $v)
				$getFields[] = "$k=$v";
				
			$this->view->redirect = $this->_request->getPathInfo() . '?' . join('&', $getFields);
		}
		
		if (preg_match('/^\/auth/', $this->view->redirect))
			$this->view->redirect = '/';
	}
	
	private function check_ie6() {
		$ua = $_SERVER['HTTP_USER_AGENT'];
		
		if (strpos($ua,'MSIE') !== false && strpos($ua,'Opera') === false) {
			if (strpos($ua,'Windows NT 5.2') !== false)
			{
				if(strpos($ua,'.NET CLR') === false) 
					return;
			}
			
			//if (substr($ua,strpos($ua,'MSIE')+5,1) < 6) {
		//		header("Location: /static/upgrade_ie");
	//			exit;
			//}
		}
	}
	
	public function logoutAction() {
		$auth = Zend_Auth::getInstance();
		
		if ($auth->hasIdentity())
			$auth->clearIdentity();
			
		Zend_Session::destroy(); // WRECK
			
		$this->_redirect('/');
	}
}