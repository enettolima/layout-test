<?php
class IndexController extends Zend_Controller_Action
{
	private $newsModel;
	
	public function init()
	{
		$baseUrl = $this->_request->getBaseUrl();
		$this->newsModel = new NewsModel();
	}

	public function indexAction()
	{
		$auth = Zend_Auth::getInstance();
		$this->view->identity = $auth->getIdentity();
		 
		$this->view->articles = $this->newsModel->getArticlesByPage(0, 5, 'POSTED_ON', 'DESC');
	}
}
?>