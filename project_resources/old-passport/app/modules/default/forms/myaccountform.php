<?php
class forms_MyAccountForm extends Zend_Form {
	public function init() {
		$this->setName('edit_user');
		
		// Username Field - Text
		$username = new Zend_Form_Element_Text('username');
		$username
			->setLabel('Username')
			->setRequired(true)
			->addValidator('NotEmpty')
			->addValidator('Alnum')
			->addValidator('StringLength', array(3, 32));
		
		// Password Field - Password
		$this->addElement('password', 'password',
			array(
				'label' => "New Password",
				'validators' => array(
					array('StringLength', 20, 6),
				)
			)
		);
		
		// Password Confirm Field - Password
		$this->addElement('password', 'confirmpassword',
			array(
				'label' => "Confirm Password",
				'validators' => array(
					array('StringLength', 20, 4),
					array('CompareToField', false, array('password'))
				)
			)
		);
					
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
			
		$submit = new Zend_Form_Element_Submit('save_user');
		$submit
			->setLabel('Save')
			->setDecorators(array( 
			'ViewHelper',
			array('HtmlTag', array('tag' => 'div'))));
			
		$this->setElementFilters(array('StringTrim'));
		
		$this->addElements(array($username, $first_name, $last_name));
		
		// Display Groups
		$this->addDisplayGroup(array('username', 'password', 'confirmpassword'), 'Account');
		$this->addDisplayGroup(array('first_name', 'last_name'), 'Personal');
		
		$this->addElement($submit);
	}
}