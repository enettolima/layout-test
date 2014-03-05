<?php
require_once(APP_ROOT . '/models/User.php');
require_once(APP_ROOT . '/models/DCS.php');
require_once(APP_ROOT . '/models/Item.php');

class ItemsController extends Zend_Controller_Action
{
    private $itemsModel;
    private $searchModel;

    function init() {
        $this->itemsModel = new ItemsModel();
        $this->searchModel = new ItemSearchModel();

        $auth = Zend_Auth::getInstance();
        $this->view->identity = $auth->getIdentity();
    }

    function indexAction() {
        $page = intval(trim($this->_request->getParam('page', 0)));
        $pageSize = intval(trim($this->_request->getParam('size', 20)));

        $items = array();
        if ($this->_request->getParam('q')) {
            $queryString = "+".$this->_request->getParam('q');

            if ($this->_request->getParam('image'))
                $queryString .= " +image:1";

            $items = Item::findBySearch($queryString);
        } else if ($this->_request->getParam('dcs')) {
        	$items = Item::findByDCS(urldecode($this->_request->getParam('dcs')));
        }
        
        $this->view->items = $items;
        $this->view->dcsTree = DCS::findTree();
    }
}