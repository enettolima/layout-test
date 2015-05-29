<?php

class UploaderController extends BaseController {

/**
	 * Add new file from the file uploader.
	 *
	 * @return Response
	 */
	public function postUploadFile()
	{
		$data 		= Input::All();
		print_r($data);
		$file = Input::file('myfile');
		//checking if myfile is valid
		if (!Input::file('myfile')->isValid())
		{
			$error['message'] = "Testing error";
			return Response::json($error, 500);
		}

		$name = Input::file('myfile')->getClientOriginalName();
		$file_name = pathinfo($name, PATHINFO_FILENAME); // file
		$extension = pathinfo($name, PATHINFO_EXTENSION); // jpg
		/*
		* In the future if we need to validate the format of the file
			if($extension!="pdf"){
			$error['message'] = "Only PDF files are allowed in this upload! Please try again!";
			return Response::json($error, 500);
		}*/

		// Upload to this temporary directory.
		$destination_path 	= public_path().'/file_upload/';

		// Check if Directory exists.
		if (!is_dir($destination_path)) {
			$error['message'] = "Unable to create directory!";
			return Response::json($error, 500);
		}
		Input::file('myfile')->move($destination_path, $name);
		$results = array(
			"limit" => 20,
			"id" => "3127"
		);

		//html to be returned to the form to allow the user to delete the file.
		$upload_element = '<li class="file-item well">
			<span class="file-name">'.$name.'</span>
			<a id="file-item-3127" class="file-delete" data-info-id="3127" title="Delete" onClick="uploader_remove_file(3127, 3127);">
				<i class="fa fa-times fa-lg"></i>
			</a>
			<input name="file_id[]" class="hide" type="checkbox" checked="checked" value="'.$name.'" />
		</li>';

		$breaks       = array("\r\n", "\n", "\r");
		$file_item    = str_replace($breaks, "", $upload_element);
		$file_item    = str_replace('"', "'", $upload_element);
		$results['file_item'] = htmlentities($file_item);
		//return Response::json($results);
		print json_encode($results);
	}

/**
	 * Remove the specified resource from storage.
	 *
	 * @return Response
	 */
	public function postRemoveUploadedFiles()
	{
    $data = array('removed' => TRUE);
    print json_encode($data);

	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @return Response
	 */
	public function getRemoveUploadedFiles()
	{
    $data = array('removed' => TRUE);
    print json_encode($data);

	}

}
