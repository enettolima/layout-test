<?php

class OrdersController extends Zend_Controller_Action 
{
	private $ordersModel;
	private $itemsModel;
	
	public $ajaxable = array(
        'show' => array('html'),
		'add-items' => array('html')
    );
    
    private $orderStages = array(
			1 => 'Stage 1: Order Placement', 
			2 => 'Stage 2: Item Approval',
			3 => 'Stage 3: Order Packing',
			4 => 'Stage 4: Order Shipping');
	
	function init() {
		$this->ordersModel = new OrdersModel();
		$this->itemsModel = new ItemsModel();
		
        $this->_helper->ajaxContext()->initContext();
	}
	
	function indexAction() {
		list($thisWeek, $thisYear) = Order::findMaxWeek();
		
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$count = Order::countReadable($user);
		if ($count > 100) {
			$week = $this->_request->getParam('week', $thisWeek);
			$year = $this->_request->getParam('year', $thisYear);
			
			if ($week < 0 || ($week > $thisWeek && $year == $thisYear) || $year < 2008 || $year > $thisYear || $week > 52) {
				$week = $thisWeek;
				$year = $thisYear;
			}
			
			$this->view->orders = Order::findReadableByWeek($user, $week, $year);
			
			$this->view->week = $week;
			$this->view->year = $year;
		} else {
			$this->view->orders = array();
			
			$year = $thisYear;
			$week = $thisWeek;
			for($i = 0; $i < 4; $i++) {
				$key = "Week $week : $year";
				$this->view->orders[$key] = array();
				
				if ($week-- == 1) {
					$week = 52;
					$year--;
				}
			}
			
			$orders = Order::findReadable($user);
			foreach ($orders as $order) {
				$key = "Week " . date('W : Y', strtotime($order->created_on));
				if (!isset($this->view->orders[$key]))
					$this->view->orders[$key] = array();
					
				$this->view->orders[$key][] = $order;
			}
		}
		
		$this->view->this_week = $thisWeek;
		$this->view->this_year = $thisYear;
	}
	
	function addAction() {
		$user = Zend_Auth::getInstance()->getIdentity();
		$order_id = $this->_request->getParam('order');
		$item_qty = $this->_request->getParam('item_qty', array());
		
		if (!isset($order_id)) {
			$this->_redirect('/error/');
			return;
		}
		
		if ($c = Cart::findByUser($user)) {
			$q = Doctrine_Query::create()
				->from('Order o')
				->where('o.id = ?', array($order_id))
				->execute();
			
			if (!empty($q)) {
				$order = $q[0];
			}

			$already_exists = array();
			
			if (isset($order) && $order->canWrite($user)) {
				foreach ($item_qty as $item_no => $qty) {
					try {
						$oi = new OrderItem;
						$oi->order_id = $order->id;
						$oi->order_stage = $order->max_stage;
						$oi->item_no = $item_no;
						$oi->item_qty = $qty;
						
						$oi->save();
						
						Doctrine_Query::create()
							->delete()
							->from('CartItem ci')
							->where('ci.item_no = ? and ci.cart_id = ?', array($item_no, $c->id))
							->execute();
					} catch (Exception $e) {
						$already_exists[] = $item_no;
					}
				}
			}
			
			if (empty($already_exists))
				$c->delete();
			else {
				
			}
		}
		
		$this->_redirect('/orders/show/order/' . $order_id);
	}
	
	function createAction() {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$sm = new StoreModel;
		$stores = array_keys($sm->getAvailableStores($user->id));
		
		if (count($stores) == 1) {
			$this->ordersModel->createOrder($stores[0], 1);
			$this->_redirect('/orders');
		} else {
			// Store selector interface
		}
	}
	
	function showAction() {
		$page = $this->_request->getParam('page', 0);
		$pageSize = $this->_request->getParam('page_size', 0);
		$orderCol = 'MARKDOWN';
		$order = 'DESC';
		$messages = array();
		
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$orderId = $this->_request->getParam('order');
		if (!isset($orderId)) {
			$this->_redirect('/error/');
			return;
		}
		
		$order = $this->ordersModel->getOrder($orderId);
		if (isset($order)) {
			$order->items = $this->ordersModel->getItemsByPage($orderId, $page, $pageSize, $orderCol, $order);
			$order->access = $this->ordersModel->getAccess($user->id, $orderId);
		} else {
			$this->_redirect('/error/');
			return;
		}
		
		$this->view->order = $order;
		if ($this->_request->getParam('print', 0)) {
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer('show.print');
			
		}
	}
	
	function historyAction() {
		$page = 0;
		$pageSize = 0;
		
		$orderId = $this->_request->getParam('order', false);
		
		if ($orderId === false) {
			$this->_redirect('/error/');
			return;
		}
		
		$orderInfo = $this->ordersModel->getOrder($orderId);
		if (!$orderInfo) {
			$this->_redirect('/error/');
			return;
		}
		
		$orderStages = $this->ordersModel->getOrderStages($orderId, $orderInfo->ORDER_STAGE);
		
		if ($orderStages) {
			$this->view->order = $orderInfo;
			$this->view->orderStages = $orderStages;
		}
	}
	
	function updateAction() {
		$orderId = $this->_request->getParam('order', false);
		
		if ($orderId !== false && $this->_request->isPost()) {
			$updateItems = array();
			$comments = array();
			
			foreach ($this->_request->getParams() as $key => $val) {
				if (substr($key, -4) == '_qty')
					$updateItems[intval(substr($key, 0, -4))] = intval($val);
				else if (substr($key, -8) == '_comment')
					$comments[intval(substr($key, 0, -8))] = trim(htmlentities($val));
			}
							
			foreach ($comments as $itemNo => $comment) {
				if (empty($comment))
					$this->ordersModel->deleteItemComment($orderId, $itemNo);
				else
					$this->ordersModel->saveItemComment($orderId, $itemNo, $comment);
			}
			
			$this->ordersModel->updateItems($orderId, $updateItems);
			$locked = $this->_request->getParam('locked') == 'on';
			$this->ordersModel->setLock($orderId, $locked);
		} 
		
		$this->_forward('show');
	}
	
	function deleteItemAction() {
		$orderId = $this->_request->getParam('order', false);
		$itemNo = $this->_request->getParam('item', false);
		
		if ($orderId !== false) {
			$deleteItems = array();
			
			if ($itemNo !== false)
				$deleteItems[] = $itemNo;
			else if ($this->_request->isPost()) {
				foreach ($this->_request->getParams() as $key => $val) {
					if (substr($key, -4) == '_chk')
						$deleteItems[] = intval(substr($key, 0, -4));
				}
			}
				
			if (!empty($deleteItems))
				$this->ordersModel->deleteItems($orderId, $deleteItems);
		}
		
		$this->_forward('show');
	}
}