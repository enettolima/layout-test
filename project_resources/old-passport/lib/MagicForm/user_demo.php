<?php
require_once('MagicForm.php');

function testAction() {	
	$stores = array('382' => 'Athens', '325' => 'Grapevine', '318' => 'Mesquite', '101' => 'Llama Land');
	$stages = array('Propose', 'Store Review', 'Buyer Review', 'Shipping');
	$roles = array('Manager', 'Administrator', 'HR', 'District Manager');
	
	$formData = array(
		'username' => "",
		'first_name' => "",
		'last_name' => "");
	
	$form = new MagicForm('add_user');

	/*if (count($_POST)) {
		try {
			if ($form->isValid())
				echo "LOLVALID";
			else 
				$data = (object)$_POST;
		} catch(MagicFormException $e) {
			$form->reset();
			var_dump($e);
		}
	}*/
	
	echo "<pre>";
	var_dump($_POST);
	echo "</pre>";
	
	// Render the view - this could all be done with a zend_view plugin
	ob_start();
	include ('user_view.phtml');
	$output = ob_get_contents();
	ob_end_clean();
	
	// Filter the rendered data
	echo $form->wand($output);
}

testAction();