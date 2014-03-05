<?php
require_once('AclModel.php');

class forms_EditUserForm extends Zend_Form {
	public function __construct() {
		parent::__construct();
		$this->setName('edit_user');
		
		$aclModel = new AclModel;
		$storeModel = new StoreModel;
		
		// Username Field - Text
		$username = new Zend_Form_Element_Text('username');
		$username
			->setLabel('Username')
			->setRequired(true)
			->addValidator('NotEmpty')
			->addValidator('Alnum')
			->addValidator('StringLength', array(3, 32));
		
		// Password Field - Password
		$password = new Zend_Form_Element_Text('password');
		$password
			->setLabel('Password')
			->setRequired(true)
			->addValidator('NotEmpty')
			->addValidator('StringLength', array(3, 32));
		
		// Password Confirm Field - Password
		$password2 = new Zend_Form_Element_Text('password2');
		$password2
			->setLabel('Confirm Password')
			->setRequired(true)
			->addValidator('NotEmpty')
			->addValidator('StringLength', array(6, 16))
			->addValidator('Identical', 'password');
				
		// First Name Field - Text
		$first_name = new Zend_Form_Element_Text('first_name');
		$first_name
			->setLabel('First Name')
			->setRequired(true)
			->addValidator('NotEmpty')
			->addValidator('StringLength', array(3, 32));
				
		// Last Name Field - Text
		$last_name = new Zend_Form_Element_Text('last_name');
		$last_name
			->setLabel('Last Name')
			->setRequired(true)
			->addValidator('NotEmpty')
			->addValidator('StringLength', array(3, 32));
				
		// Expire on - Date
		$expires_on = new Zend_Form_Element_Text('expires_on');
		$expires_on
			->setLabel('Expire')
			->setRequired(false);
			
		$arrayRoles = array();
		foreach($aclModel->getRoles() as $role) {
			$arrayRoles[$role->ROLE_ID] = $role->ROLE_NAME;
		}
			
		// Role Field - Select
		$role_id = new Zend_Form_Element_Select('role_id');
		$role_id
			->setLabel('Role')
			->setMultiOptions($arrayRoles)
			->setRequired(true)
			->addValidator('NotEmpty');
			
		// Store Access - Array of MultiCheckbox
		$stores = $storeModel->getStores();
		var_dump($stores);
		$test = new Zend_Form_Element_MultiCheckbox('test_232');
		$test->addMultiOptions(array('read' => 'Read', 'write' => 'Write'));
		
		$this->setElementFilters(array('StringTrim'));
		
		$this->addElements(array(
			$username, $password, $password2,
			$expires_on, $first_name, $last_name));
		
		// Display Groups
		$this->addDisplayGroup(array('username', 'password', 'password2', 'expires_on'), 'Account');
		$this->addDisplayGroup(array('first_name', 'last_name'), 'Personal');
		$this->addElement($role_id);
		$this->addElement($test);
	}

}