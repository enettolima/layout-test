<?php
class Hr_EmployeesController extends Zend_Controller_Action implements ISecureController {
	private $employeesModel, $storeModel, $auth;
	
	function init() {
		$this->employeesModel = new EmployeesModel;
		$this->storeModel = new StoreModel;
		
		$auth = Zend_Auth::getInstance();
		$this->identity = $auth->getIdentity();
		$this->view->identity = $auth->getIdentity();
	}
	
	function indexAction() {
		$this->view->employees = $this->employeesModel->getAvailableEmployees($this->identity->USER_ID);
	}
	
	function newHireAction() {
		$stores = $this->storeModel->getAvailableStores($this->identity->USER_ID);
		$form = new forms_NewHireForm(array('stores' => $stores));
		
		 if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            
            if ($form->isValid($formData)) {
            	$values = $form->getValues();
                $this->employeesModel->saveEmployee($values);
                $this->_redirect('/hr/employees/confirm');
            } else {
            	$form->populate($formData);
            }
        } else if ($employeeData = $this->employeesModel->restoreEmployee())
        	$form->populate($employeeData);
		
		$this->view->newHireForm = $form;
	}
	
	function editAction() {
		$stores = $this->storeModel->getAvailableStores($this->identity->USER_ID);
		$emplId = $this->_request->getParam('id', false);
		$form = new forms_EditEmployeeForm(array('stores' => $stores));
		
		if (!$emplId) {
			$this->_redirect('/hr/employees');
			return;
		}

		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			
            if ($form->isValid($formData)) {
            	$values = $form->getValues();
            	if (substr($values['ssn'], 0, 7) == 'XXX-XX-')
            		unset($values['ssn']);
            	
                $this->employeesModel->updateEmployee($emplId, $values);
            }
		}
		
		if ($employeeData = $this->employeesModel->getEmployee($emplId)) {
			$formData = array();
						
			foreach ((array)$employeeData as $key => $val)
				$formData[strtolower($key)] = $val;
			
			$formData['ssn'] = "XXX-XX-" . $formData['ssn_last4'];
			unset($formData['ssn_last4']);
			
			$form->populate($formData);
		} else {
			$this->_redirect('/error/');
			return;
		}
		
		$this->view->employeeData = $employeeData;
		$this->view->editEmployeeForm = $form;
	}
	
	function confirmAction() {
		if (!$employeeData = $this->employeesModel->restoreEmployee()) {
			$this->_redirect("/hr/employees/new-hire");
			return;
		}
		
		if ($this->_request->isPost() && $this->_request->getParam('confirm_newhire')) {
			$emplId = $this->employeesModel->createEmployee($employeeData);
			$this->employeesModel->clearEmployee();
			$this->_redirect("/hr/employees/show/id/$emplId");
		}
		
		$this->view->employeeData = $employeeData;
	}
	
	function showAction() {
		$emplId = $this->_request->getParam('id', false);
		
		if (!$emplId) {
			$this->_redirect('/hr/employees');
			return;
		}

		if (!$employeeData = $this->employeesModel->getEmployee($emplId)) {
			$this->_redirect('/hr/employees');
			return;
		}
		
		$this->view->employeeData = $employeeData;
	}
}