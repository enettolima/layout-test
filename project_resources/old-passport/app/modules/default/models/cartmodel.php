<?php
require_once('ItemsModel.php');

class CartModel
{
	private $items;
	private $session;
	
	function __construct() {
		$this->session = new Zend_Session_Namespace('CartModel');
		
		if (isset($this->session->items))
			$this->items = $this->session->items;
		else
			$this->items = array();
	}
	
	function save()	{
		$this->session->items = $this->items;
	}
	
	function getItems()	{
		$ret = array_values($this->items);
		//sort
		
		return $ret;
	}
	
	function addItem($item_no) {
		if (!ctype_digit($item_no)) {
			throw(new Exception("Item Number $item_no is not valid."));
			return;
		}
		
		if (isset($this->items[$item_no]))
			$this->items[$item_no]->qty++;
		else {
			$im = new ItemsModel;
			$item = $im->getItemInfo($item_no);
			$item->qty = 0;
			
			$this->items[$item_no] = $item;
		}
	}
	
	function removeItem($item_no) {
		if (!ctype_digit($item_no)) {
			throw(new Exception("Item Number $item_no is not valid."));
			return;
		}
		
		if (isset($this->items[$item_no]))
			unset($this->items[$item_no]);
	}

	function clear() {
		$this->items = array();	
	}
	
	function updateItems($update) {
		foreach ($update as $item_no => $qty) {
			if (!isset($this->items[$item_no]))
				continue;
			
			if ($qty < 0)
				$this->items[$item_no]->qty = max($this->items[$item_no]->qty - abs($qty), 0);
			else if ($qty == 0)
				unset($this->items[$item_no]);
			else
				$this->items[$item_no]->qty = $qty;
		}
	}
}