<?php
class RolesController extends Zend_Controller_Action {
	public function indexAction() {
		$this->view->roles = Role::findAll();
	}
	
	public function editAction() {
		$f = new MagicForm('edit_role');
		
		$role_id = $this->_request->getParam('role', false);
		if (!$role_id) {
			$this->_redirect("/roles");
			return;
		}
		
		$role = Role::find($role_id);
		if (!$role) {
			$this->_redirect("/roles");
			return;
		}
	
		if (empty($_POST)) {
			$this->role = $role->toArray();
		}else {
			$postData = $_POST;

			unset($postData['id']); // Just incase
			
			try {
				if ($f->isValid()) {
					$role->synchronizeWithArray($postData);
					$role->save();
				}
			} catch (Exception $e) {
				$this->view->messages = array("Unable to save role '{$postData['name']}'.");
			}
			
			$this->view->role = $postData;
		}
		
		$this->view->resources = Resources::findAll();
	}
	
	public function createAction() {
		$f = new MagicForm('create_role');
		
		if (!empty($_POST)) {
			$postData = $_POST;

			unset($postData['id']); // Just incase
			
			try {
				if ($f->isValid()) {
					$r = new Role;				
					$r->fromArray($postData);
					$r->save();
					
					$this->_redirect('/roles/edit/role/' . $r->id);
					return;
				}
			} catch (Exception $e) {
				$this->view->messages = array("Unable to create role '{$postData['name']}'");
			}
			
			$this->view->role = $postData;
		} else {
			$this->view->role = array(
				'name' => '');
		}
	}
	
	public function deleteAction() {
		$role_id = $this->_request->getParam('role', false);
		
		if ($role_id && ($role = User::find($role_id))) {
			$role->delete();
		}
		
		$this->_redirect('/roles');
	}
}