<?php

class ToolsController extends BaseController
{
    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
    }

    public function getIndex()
    {
        return Redirect::to('/');
    }

    public function getEmployeeLookup()
    {
        return View::make(
            'pages.tools.employeelookup',
            array (
                'extraJS' => array(
                    '/js/tools/employeelookup/employeelookup.js',
                ),
            )
        );
    }
}
