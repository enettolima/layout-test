<?php
class NewsController extends Zend_Controller_Action {
	private $newsModel;
	
	function init()
	{
		$this->newsModel = new NewsModel();
		
		$auth = Zend_Auth::getInstance();
		$this->view->identity = $auth->getIdentity();
	}
	
	function indexAction()
	{
		$this->view->title = "News Archives";
		
		$page = 0;
		$pageSize = 10;
		
		if ($this->_request->getParam('page'))
			$page = (int)($this->_request->getParam('page'));
			
		if ($this->_request->getParam('page_size'))
			$pageSize = (int)($this->_request->getParam('page_size'));
			
		$articles = $this->newsModel->getArticlesByPage($page, $pageSize, 'POSTED_ON', 'DESC');
		if (!isset($articles))
		{
			$this->_redirect('/error/');
			return;
		}
		
		$articleCount = $this->newsModel->getArticlesCount();
		if ($articleCount === false)
		{
			$this->_redirect('/error/');
			return;
		}
		
		$this->view->articles = $articles;
		$this->view->page = $page;
		$this->view->pageSize = $pageSize;
		$this->view->pageCount = ceil($articleCount / $pageSize) - 1;
	}
	
	function editAction()
	{
		$this->view->title = "Edit Article";
		//$this->view->messages = array();
		
		$article_id = null;
		if ($this->_request->getParam('article'))
			$article_id = intval($this->_request->getParam('article'));
		else
		{
			$this->_redirect('/error/');
			return;
		}
		
		if ($this->_request->isPost())
		{				
			$update = array(
				'SUBJECT' => htmlentities(trim($this->_request->getParam('subject'))), 
				'TYPE' => intval($this->_request->getParam('type')), 
				'BODY' => trim($this->_request->getParam('body'))
				);
				
			if (!$this->newsModel->updateArticle($article_id, $update))
			{
				$this->_redirect('/error/');
				return;
			}
		}
		
		$this->view->article = $this->newsModel->getArticle($article_id);
	}
	
	function createAction()
	{
		$this->view->title = "Create Article";
		//$this->view->messages = array();
		
		$user_id = $this->view->identity->USER_ID;
		
		if ($article_id = $this->newsModel->createArticle($user_id))
			$this->_redirect("/news/edit/article/$article_id");
		else 
			$this->_redirect('/error/');
	}
	
	function deleteAction()
	{
		$article_id = (int)($this->_request->getParam('article'));
		
		if ($this->newsModel->deleteArticle($article_id))
			$this->_redirect('/news/');
		else 
			$this->_redirect('/error/');
	}
}
?>