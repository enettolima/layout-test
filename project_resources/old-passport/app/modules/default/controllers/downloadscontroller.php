<?php
class DownloadsController extends Zend_Controller_Action {
	public function indexAction() {
		$cwd = urldecode($this->_request->getParam('cwd', ''));
		$layout = $this->_request->getParam('layout', 'small');
		$sort = $this->_request->getParam('sort', 'filename');
		$direction = $this->_request->getParam('direction', 'down');
		
		$dm = new DownloadsModel($cwd, $this->view->identity);
		
		$user = Zend_Auth::getInstance()->getIdentity();
		$acl = PassportAcl::getInstance($user);
		
		$this->view->files = $dm->getFiles($sort, $direction == 'down');
		$this->view->cwd = $dm->getCwd(true);
		$this->view->layout = $layout;
		$this->view->sort = (object)array('field' => $sort, 'direction' => $direction);
		$this->view->can_upload = $dm->isAllowed(DownloadsModel::ACTION_UPLOAD) && $acl->isAllowed('default', 'downloads', 'upload');
	}
	
	public function downloadAction() {
		$cwd = rtrim(urldecode($this->_request->getParam('cwd', '')), '/') . '/';
		$file = urldecode($this->_request->getParam('file', ''));
		
		if (empty($file)) {
			$this->_redirect('/error');
			return;
		}
		
		$dm = new DownloadsModel($cwd, $this->view->identity);
		$dm->downloadFile($file);
	}
	
	public function uploadAction() {
		$cwd = rtrim(urldecode($this->_request->getParam('cwd', '')), '/') . '/';
				
		$dm = new DownloadsModel($cwd, $this->view->identity);
		
		if (isset($_FILES['uploadedfile']['tmp_name'])) {
			$dm->moveFile($_FILES['uploadedfile']['tmp_name'], basename($_FILES['uploadedfile']['name']));
			$this->_redirect('/downloads?cwd=' . urlencode($cwd));
		}
	}
}