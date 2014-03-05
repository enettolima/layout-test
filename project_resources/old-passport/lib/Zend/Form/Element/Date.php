<?php
class Zend_Form_Element_Date extends Zend_Form_Element {
// TODO: Redo this class
	function __construct($options) {
		parent::__construct($options);
			
		//$this->setDescription('<img src="" alt="cal icon" />');
	}
	
	public function loadDefaultDecorators() {
		$this->addDecorator(array(
        	'CalIcon' => 'HtmlTag'), array(
        		'tag' => 'img', 
				'src' => '/images/icons/calendar_18.png',
        		'placement' => 'append'));
		$this->addDecorator(array(
        	'CalLink' => 'HtmlTag'), array(
        		'tag' => 'a',
				'class' => 'date_select',
        		'id' => $this->_name . '_link'));
		
		$this->addDecorator(array(
        	'YearSelect' => 'HtmlTag'), array(
        		'tag' => 'select', 
        		'placement' => 'prepend',
        		'id' => $this->_name . '_year',
        		'name' => $this->_name . '_year'));
		$this->addDecorator(array(
        	'MonthSelect' => 'HtmlTag'), array(
        		'tag' => 'select', 
        		'placement' => 'prepend',
        		'id' => $this->_name . '_date',
        		'name' => $this->_name . '_date'));
		$this->addDecorator(array(
        	'DateSelect' => 'HtmlTag'), array(
        		'tag' => 'select', 
        		'placement' => 'prepend',
        		'id' => $this->_name . '_month',
        		'name' => $this->_name . '_month'));
        
        $this->addDecorator('Errors');
        $this->addDecorator('HtmlTag', array('tag' => 'dd'));
        $this->addDecorator('Label', array('tag' => 'dt'));
    }   
}