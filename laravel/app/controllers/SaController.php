<?php

class SaController extends BaseController
{
    public function getStores()
    {
        $user = Request::segment(3);
        $foo = array('chad', 'erika', 'netto', 'marcelo');
        return Response::json($foo);
    }

}