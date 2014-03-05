<?php
class forms_EditEmployeeForm extends forms_NewHireForm {
	public function __construct($options = null) {
		parent::__construct($options);

		$submit = new Zend_Form_Element_Submit('save_employee');
		$submit
			->setLabel('Save New Hire')
			->setDecorators(array( 
			'ViewHelper',
			array('HtmlTag', array('tag' => 'div'))));
			
		$inPaychex = new Zend_Form_Element_Checkbox('chk_inpaychex');
		$inPaychex
			->setLabel('In Paychex?');
			
		$paperRecieved = new Zend_Form_Element_Checkbox('chk_taxform');
		$paperRecieved
			->setLabel('Forms Recieved?');
			
		$this->removeElement('create_newhire');
		$this->addElements(array($inPaychex, $paperRecieved));
		$this->addDisplayGroup(array('chk_inpaychex', 'chk_taxform'), 'Payroll');
		
		$this->addElement($submit);
	}
}