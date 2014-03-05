<?php
require_once(APP_ROOT . '/models/User.php');
require_once(APP_ROOT . '/models/Cart.php');
require_once(APP_ROOT . '/models/CartItem.php');
require_once(APP_ROOT . '/models/Item.php');

class CartController extends Zend_Controller_Action
{
	function init() {

	}
	
	function indexAction() {
		$user = Zend_Auth::getInstance()->getIdentity();
		$this->_helper->layout->disableLayout();
		
    	$c = Cart::findByUser($user);	
		$this->view->cart = $c;
		
		if (Order::countWritable($user) == 1) {
			$orders = Order::findWritable($user);
			$this->view->order = $orders[0];
		}
	}
	
	function addAction() {
		$user = Zend_Auth::getInstance()->getIdentity();
		$item_no = intval($this->_request->getParam('item'));
		$qty = intval($this->_request->getParam('qty', 1));
		
		try {
			if (!$c = Cart::findByUser($user)) {
				echo "HERE";
				$c = new Cart;
				$c->user_id = $user->id;
				$c->save();
				$c->refresh();
			}
			
			$ci = new CartItem;
			$ci->item_no = $item_no;
			$ci->cart_id = $c->id;
			$ci->qty = $qty;
			$ci->save();
		} catch (Exception $e) {
			$this->view->messages = array($e->getMessage());
		}

		$this->_redirect('/cart');
	}
	
	function saveAction() {
		$user = Zend_Auth::getInstance()->getIdentity();
		$item_qty = $this->_request->getParam('item_qty', array());
		$order_id = $this->_request->getParam('order_id', false);

		try {
			if ($c = Cart::findByUser($user)) {
				Doctrine_Query::create()
					->delete()
					->from('CartItem ci')
					->where('ci.cart_id = ?', array($c->id))
					->execute();
				
				foreach ($item_qty as $item_no => $qty) {
					$ci = new CartItem;
					$ci->item_no = $item_no;
					$ci->cart_id = $c->id;
					$ci->qty = $qty;
					
					$ci->save();
				}
			}
		} catch (Exception $e) {
			$this->view->messages = array($e->getMessage());
		}
		
		$this->_redirect('/cart');
	}
	
	function deleteAction() {
		$user = Zend_Auth::getInstance()->getIdentity();
		$item_no = intval($this->_request->getParam('item'));

		try {
			if ($c = Cart::findByUser($user)) {
				Doctrine_Query::create()
					->delete()
					->from('CartItem ci')
					->where('ci.cart_id = ? and ci.item_no = ?', array($c->id, $item_no))
					->execute();
			}
		} catch (Exception $e) {
			$this->view->messages = array($e->getMessage());
			var_dump($this->view->messages);
			exit;
		}
		
		$this->_redirect('/cart');
	}

	function clearAction() {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		try {
			if ($c = Cart::findByUser($user)) {
				$c->delete();
			
			}
		}catch (Exception $e) {
			$this->view->messages = array($e->getMessage());
		}
		
		$this->_redirect('/cart');
	}
}