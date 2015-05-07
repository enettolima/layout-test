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
					'extraJS' => array (
						'/js/elasticsearch.jquery.min.js',
						'/js/jstree.min.js',
						'/js/docs.js'
					),
					'extraCSS' => array (
						'/css/jstree.css'
					)
        ));
    }

    public function getTemplate()
    {
        return View::make('pages.documents.template', array(
            // 'extraJS' => array ('/js/elasticsearch.jquery.min.js')
        ));
    }
}
