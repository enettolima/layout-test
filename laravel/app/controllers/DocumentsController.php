<?php

class DocumentsController extends BaseController 
{
    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
    }

    public function getIndex()
    {
        return View::make('pages.documents.index', array(
            'extraJS' => array ('/js/elasticsearch.jquery.min.js')
        ));
    }
}
