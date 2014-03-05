<?php
require_once('MagicForm.php');

function testAction() {	
	$delivery_types = array('Shipping', 'Download');
		
	$form = new MagicForm('my_form');

	if (count($_POST)) {
		try {
			if ($form->isValid())
				echo "LOLVALID";
			else 
				$data = (object)$_POST;
		} catch(MagicFormException $e) {
			$form->reset();
			var_dump($e);
		}
	}
	// Render the view - this could all be done with a zend_view plugin
	ob_start();
	include ('demo2_view.phtml');
	$output = ob_get_contents();
	ob_end_clean();
	
	// Filter the rendered data
	echo $form->wand($output);
}

testAction();