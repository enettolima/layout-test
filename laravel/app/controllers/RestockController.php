<?php

class RestockController extends BaseController
{
    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
    }

    public function getIndex()
    {
        return Redirect::to('/restock/browse');
    }

    public function getBrowse()
    {
        return View::make(
            'pages.restock.browse',
            array(
                'extraJS' => array(
                    '/js/restock/restock.js'
                )
            )
        );
    }

    public function getCarts()
    {
        return View::make('pages.restock.carts');
    }

    public function getOrders()
    {
        return View::make('pages.restock.orders');
    }

}
