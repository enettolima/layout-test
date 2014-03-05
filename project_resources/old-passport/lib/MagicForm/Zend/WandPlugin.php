<?php
require_once('MagicForm.php');

class MagicForm_Zend_WandPlugin extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopShutdown() {
		if (MagicForm::wasUsed())
        	$this->getResponse()->setBody(MagicForm::wand($this->getResponse()->getBody())); 
    }
}