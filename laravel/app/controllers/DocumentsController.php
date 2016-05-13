<?php

class DocumentsController extends BaseController
{
    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
        $this->maintenance = false;
    }

    public function getIndex()
    {
      if($this->maintenance){
        return View::make('pages.maintenance',
          array(
            "title" => "Document Search is currently down for maintenance.",
            "message" => "Thank you for your patience and sorry for the inconvenience."
        ));
      }else{
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
    }


    public function getTemplate()
    {
        return View::make('pages.documents.template', array(
            // 'extraJS' => array ('/js/elasticsearch.jquery.min.js')
        ));
    }

    public function getLoadFile()
    {
        //get request
        $data             = Input::all();
        $path = public_path();

        //$data             = Input::all();
        //$real_data = json_decode($data['data'], true);
        $api  = new EBTAPI;
  			$json	= $api->put('/esdocs/update-clicks', $data);
        //return Response::json($json);

        //Log::info('Document URL '.$_ENV['ebt_link_storage'].str_replace("/media/web","",$data['path']));
        //Log::info('Location should be '.$path.$_ENV['ebt_link_storage'].$data['path'].' -- ES ID: '.$data['id']);
        //$clean_url = str_replace("/media/web","",$data['path']);
        header("Location: ".$_ENV['ebt_link_storage'].str_replace("/media/web","",$data['path']), true);
        exit();
    }
}
