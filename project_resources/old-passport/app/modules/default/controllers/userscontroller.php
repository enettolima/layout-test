<?php
require_once('MagicForm.php');

// Load the models we will be using
require_once(APP_ROOT . '/models/User.php');
require_once(APP_ROOT . '/models/Store.php');
require_once(APP_ROOT . '/models/Stage.php');
require_once(APP_ROOT . '/models/Role.php');
require_once(APP_ROOT . '/models/StoreAccessRule.php');
require_once(APP_ROOT . '/models/StageAccessRule.php');

class UsersController extends Zend_Controller_Action {
	function indexAction()
	{
		$page = $this->_request->getParam('page', 1);
		$pageSize = $this->_request->getParam('page_size', 50);
		$sort = $this->_request->getParam('sort', 'u');
		$order = $this->_request->getParam('order', 'asc');
			
		$columns = array(
			'n' => 'u.first_name', 
			'u' => 'u.username', 
			'r' => 'r.name');
		
		if (!isset($columns[$sort]))
			$sort = 'n'; 
		
		if (!in_array($order, array('asc', 'desc')))
			$order = 'asc';
			
		$this->view->users = User::findByPage($page, $pageSize, $pages, $columns[$sort], $order);
		$this->view->paging = (object)array('current' => $page, 'size' => $pageSize, 'pages' => $pages);
		$this->view->sort = $sort;
		$this->view->order = $order;
	}
	
	function editAction()
	{
		$user_id = $this->_request->getParam('user', false);
		if (!$user_id) {
			$this->_redirect('/users');
			return;
		}
		
		if (($user = User::find($user_id)) === false) {
			$this->_redirect('/users');
			return;
		}
		
		$editForm = new MagicForm('user_form');
		$passForm = new MagicForm('password_form');
		
		if (!empty($_POST)) {
			if (isset($_POST['edit_user'])) {
				$postData = $_POST;

				// Transform POST data into an array format that we can merge into the model.
				foreach (array('StoreAccessRule' => 'store_code', 'StageAccessRule' => 'order_stage') as $type => $key) {
					$tmp = array();
					if (isset($postData[$type])) {
						foreach ($postData[$type] as $stage => $access) {
							$tmp[] = array(
								$key => $stage === 'all_items' ? null : $stage,
								'user_id' => $user->id,
								'can_read' => in_array('read', $access) ? 'Y' : 'N',
								'can_write' => in_array('write', $access) ? 'Y' : 'N');
	
						}
					}
					
					$postData[$type] = $tmp;
				}
				
				unset($postData['form_key']);
				
				// Merge the new data in if it is valid
				if ($editForm->isValid()) {
					foreach (array('StoreAccessRule', 'StageAccessRule') as $type) {
						call_user_func(array($type, 'deleteByUser'), $user->id);
						foreach($postData[$type] as $rule) {
							$sar = new $type;
							$sar->fromArray($rule);
							$sar->user_id = $user->id;
							$sar->save();
						}
					}
					
					foreach ($postData as $key=>$val) {
						if (is_array($val) || !isset($user->$key))
							continue;
								
						$user->$key = $val;			
					}
					
					$user->save();
				}
				
				$this->view->user = $postData;
			} else if (isset($_POST['change_password'])) {
				if ($passForm->isValid()) {
					$user->passhash = sha1($_POST['password']);
					$user->save();
				}
				
				$this->view->user = $user->toArray(true);
			}
		} else {
			$this->view->user = $user->toArray(true);
		}
		
		$this->view->stores = Store::findAll();
		$this->view->roles = Role::findAll();
		$this->view->stages = Stage::findAll();
	}
	
	function createAction()
	{
		$f = new MagicForm('create_user');
		
		if (!empty($_POST)) {
			$postData = $_POST;
			
			$postData['passhash'] = sha1($postData['password']);
			unset($postData['password']);
			unset($postData['confirm_password']);
			unset($postData['id']); // Just incase
			
			try {
				if ($f->isValid()) {
					$u = new User;				
					$u->fromArray($postData);
					$u->role_id = 104; // guest role
					$u->save();
					
					$this->_redirect('/users/edit/user/' . $u->id);
					return;
				}
			} catch (Exception $e) {
				$this->view->messages = array("Unable to create account for user '{$postData['username']}'");
			}
			
			$this->view->user = $postData;
		} else {
			$this->view->user = array(
				'username' => '',
				'first_name' => '',
				'last_name' => '');
		}
	}
	
	function deleteAction() {
		$user_id = $this->_request->getParam('user', false);
		
		if ($user_id && ($user = User::find($user_id))) {
			$user->delete();
		}
		
		$this->_redirect('/users');
	}
}