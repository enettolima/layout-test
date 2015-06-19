<?php

class PIMSController extends BaseController
{
    public function getIndex()
    {
        return Redirect::to('/pims/product');
    }

    public function getProduct()
    {

        return View::make(
            'pages.pims.product',
            array(
                'extraJS' => array(
                    '/js/pims/pims.js',
                )
            )
        );
    } 
}
