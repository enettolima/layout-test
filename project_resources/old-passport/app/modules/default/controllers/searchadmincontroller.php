<?php
class SearchAdminController extends Zend_Controller_Action {
	var $itemSearchModel;	
	public function init()
	{
		$auth = Zend_Auth::getInstance();
		$baseUrl = $this->_request->getBaseUrl();
		$this->view->identity = $auth->getIdentity();
		$this->itemSearchModel = new ItemSearchModel();
	}

	public function indexAction()
	{
		
	}
	
	public function rebuildAction()
	{
		if ($this->itemSearchModel->runIndexBuilder())
			$this->_redirect('/searchadmin');
		else
			$this->_redirect('/error');
	}
	
	public function statusAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		echo json_encode($this->itemSearchModel->getIndexStatus());
	}
}
?>