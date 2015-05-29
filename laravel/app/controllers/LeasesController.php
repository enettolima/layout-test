<?php

class LeasesController extends BaseController {

	/* Require Auth on Everything Here */
  public function __construct()
  {
  	$this->beforeFilter('auth', array());
  }

	public function getIndex()
  {
		// get all the nerds
		//$stores = Leases::all();
	 	try {
			$api = new EBTAPI;
			$stores = $api->get('/stores/fetch-all');

			return View::make('pages.leases.index', array(
					'extraJS' => array (
					'/js/ajaxupload.js',
					'/js/uploader.js',
					'/js/leases.js'
				),
				'extraCSS' => array (
					'/css/jstree.css'
				)
			))->with('stores', $stores);
	 	} catch(Exception $e) {
			echo $e->getMessage();
			exit(1);
		}
  }

	/**
	 * Display a list of leases for a specific store
	 *
	 * @return Response
	 */
	public function getStoreInformation()
	{
		$store_code 	= $_GET['code'];

		return $this->listDocuments($store_code);
	}

	/**
	 * Creating a new lease
	 *
	 * @return Response
	 */
	public function postCreateDocument()
	{
		$data                 = Input::All();

		//Validating fields from form
		if(!isset($data['label']) || strlen($data['label'])==""){
			return $this->listDocuments($data['code'], "Field label is required! Please try again!", "alert-danger");
			exit(0);
		}
		if(!isset($data['file_id']) || count($data['file_id'])<1){
			return $this->listDocuments($data['code'], "Please select one file to upload! Please try again!", "alert-danger");
			exit(0);
		}
		//Setting the new filename and path(path from .env.local.php)
		$data['path']         = $_ENV['ebt_file_storage'].$_ENV['leases_path'];
		$ext                  = explode(".",$data['file_id'][0]);
		$extension            = $ext[1];
		$new_filename         = $data['code']."-".date("is").".".$extension;
		$data['filename']     = trim($new_filename);
		$origin               = public_path().'/file_upload/'.$data['file_id'][0];
		$destination          = $data['path']."/".$new_filename;
		//check if file exists before calling the API
		if(file_exists($origin)){
			try {
				//Moving the file to the correct path
				File::move($origin, $destination);
			} catch (Exception $e) {
				//Show the list of the files on the store information
    		//echo 'Caught exception: ',  $e->getMessage(), "\n";
				return $this->listDocuments($data['code'], "Document could not be created! Please check is the folder exists!(".$e->getMessage().")", "alert-danger");
			}
			// Delete a single file
			File::delete($origin);
			//Call the api to add the file information to the SQL Server
			$api                  = new EBTAPI;
			$json									= $api->post('/stores/create-document', $data);
			//Show the list of the files on the store information
			return $this->listDocuments($data['code'], "Document has been created!", "alert-success", true);
		}else{
			//Show the list of the files on the store information
			return $this->listDocuments($data['code'], "Document could not be created! File not found!", "alert-danger");
		}
	}

	/**
	 *Download a lease from the list
	 *
	 * @return Response
	 */
	public function getDownloadDocument()
	{
		$data                 = Input::All();
		$path         				= $_ENV['ebt_file_storage'].$_ENV['leases_path'];
		$origin  			        = $path."/".$data['file'];
		$destination          = public_path().'/file_upload/'.$data['file'];
		//check if file exists before calling the API
		if(file_exists($origin)){
			try {
				//Moving the file to the correct path
				File::copy($origin, $destination);
			} catch (Exception $e) {
				//Show the list of the files on the store information
    		//echo 'Caught exception: ',  $e->getMessage(), "\n";
				return $this->listDocuments($data['code'], "Document not found!!(".$e->getMessage().")", "alert-danger");
			}
			return Response::download($destination);
			//Show the list of the files on the store information
			//return $this->listDocuments($data['code'], "Document has been created!", "alert-success", true);
		}else{
			//Show the list of the files on the store information
			return $this->listDocuments($data['code'], "Document not found!", "alert-danger");
		}
	}

	/**
	 *Deleta a lease document from the list
	 *
	 * @return Response
	 */
	public function postDeleteDocument()
	{
		$data                 = Input::All();

		//print_r($data);
		try {
			$path         				= $_ENV['ebt_file_storage'].$_ENV['leases_path'];
			$origin  			        = $path."/".$data['doc_filename'];
			if(file_exists($origin)){
				// Delete a single file
				File::delete($origin);
			}
			//Call the api to add the file information to the SQL Server
			$api                  = new EBTAPI;
			$json									= $api->post('/stores/delete-document', $data);
			return $this->listDocuments($data['code'], "Document has been deleted!", "alert-success", true);
		} catch (Exception $e) {
			//Show the list of the files on the store information
			//echo 'Caught exception: ',  $e->getMessage(), "\n";
			return $this->listDocuments($data['code'], "Document could not be removed! Please try again!", "alert-danger");
		}
	}

	public function listDocuments($store_code, $message=null, $message_type="alert-success", $flash_item=false)
	{
		$api          = new EBTAPI;
		//$store   			= $api->get('/stores/by-store-code/?code='.$store_code);
		$store   			= $api->get('/stores/by-store-code/?code='.$store_code);
		$code_store		= $api->status_code;
		$docs         = $api->get('/stores/docs-by-store-code/?code='.$store_code);
		$code_docs		= $api->status_code;

		if($code_store==200){
			$found      = true;
			$store_name = $store[0]->store_name;
			$code       = $store[0]->code;
			$address    = $store[0]->street.", ". $store[0]->city.", ". $store[0]->state.", ". $store[0]->zip;
			$phone      = $store[0]->phone;
		}else{
			$found 		 	= false;
			$store_name = '';
			$code       = '';
			$address    = '';
			$phone      = '';
		}

		if($code_docs==200){
			$docsfound  = true;
		}else{
			$docsfound  = false;
		}
		return View::make('pages.leases.docslist', array(
			'extraJS' => array (
				'/js/ajaxupload.js',
				'/js/uploader.js',
				'/js/leases.js'
			),
			'store_info' => array (
				'store_name' => $store_name,
				'code'       => $code,
				'address'    => $address,
				'phone'      => $phone,
				'found'      => $found,
				'docs_found' => $docsfound,
				'message' 	 => $message,
				'type' 	 		 => $message_type,
				'flash'  		 => $flash_item
			)
		))->with('docs', $docs);
	}
}
