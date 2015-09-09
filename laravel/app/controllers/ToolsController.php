<?php

class ToolsController extends BaseController
{

    // Define the file specifics for the exempt form tool
    // The top-level key matches up with the html id 
    // in the form.
    protected $exemptFormFilesDef = array(
        'receiptScan' => array(
            'label' => 'Receipt Scan',
            'name' => 'receipt'
        ),
        'exemptCertScan' => array(
            'label' => 'Exemption Certification Scan',
            'name' => 'exemptcert'
        ),
        'customerIDScan' => array(
            'label' => 'Customer ID Scan',
            'name' => 'customerid'
        ),
    );


    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
    }

    public function getIndex()
    {
        return Redirect::to('/');
    }

    public function getExemptForm()
    {
        $historyDays = 30;

        return View::make(
            'pages.tools.exemptform.index',
            array (
                'extraJS' => array(
                    '/js/fileinput.js',
                    '/js/tools/exemptform/main.js',
                ),
                'extraCSS' => array(
                    '/css/fileinput.css',
                ),
                'historyDays' => $historyDays,
                'lastSubmissions' => Exemptsale::getSubmissionHistoryForCurrentStore($historyDays),
                'filesDef' => $this->exemptFormFilesDef
            )
        );
    }

    public function postExemptForm()
    {
        //$exemptDocumentationPath = storage_path() . '/exempt-documentation';
        $exemptDocumentationPath = "/media/exempt";

		$rules = array(
			'receiptNum' => 'required'
		);

		$messages = array(
			'receiptNum.required' => "You didn't provide a Receipt #!",
		);

		$validator = Validator::make(Input::all(), $rules, $messages);

		if ($validator->fails()) {
			return Redirect::to('/tools/exempt-form')->withErrors($validator)->withInput();
		} else {
            $receiptNum = Input::get('receiptNum');

            $filesDef = array(
                'exemptForm' => array(
                    'label' => 'Exempt Form',
                    'name' => 'exempt'
                ),
                'exemptForm' => array(
                    'label' => 'Exempt Form',
                    'name' => 'exempt'
                ),
                'customerID' => array(
                    'label' => 'Customer ID',
                    'name' => 'customer-id'
                ),
            );

            $filesUploaded = array();

            foreach ($this->exemptFormFilesDef as $fileKey=>$fileSettings) {
                if (Input::hasFile($fileKey)) {
                    $fileObject = Input::file($fileKey);
                    $fileNewName = $receiptNum . '-' . $fileSettings['name'] . '.' . $fileObject->getClientOriginalExtension();
                    $fileObject->move($exemptDocumentationPath, $fileNewName);
                    $filesUploaded[] = array($fileSettings['label'] => $fileNewName);
                }
            }

            $e = new Exemptsale;
            $e->empl_id = Auth::user()->username;
            $e->store_id = Session::get('storeContext');
            $e->receipt_num = $receiptNum;
            $e->files = json_encode($filesUploaded);
            $e->save();

			return Redirect::to('/tools/exempt-form-success');
        }

    }

	public function getExemptFormSuccess()
	{
		return View::make(
			'pages.tools.exemptform.success'
		);
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

    public function getMusicRequestFeedback()
    {

        $requestId = Request::segment(3);

        $request = Musicrequest::find($requestId);

        if (Auth::user()->username !== '000CD' && $request->empid !== Auth::user()->username) {
            return Response::view('pages.permissionDenied');
        }

		return View::make(
            'pages.tools.musicrequest.feedback',
            array('request' => $request)
		);
    }

	public function getMusicRequest()
	{
        $userRequests = Musicrequest::where('empid', Auth::user()->username)->get();

		return View::make(
            'pages.tools.musicrequest.index',
            array('userRequests' => $userRequests)
		);
	}

	public function postMusicRequest()
	{
		$rules = array(
			'request' => 'required'
		);

		$messages = array(
			'request.required' => "You didn't provide a request!",
		);

		$validator = Validator::make(Input::all(), $rules, $messages);

		if ($validator->fails()) {
			return Redirect::to('/tools/music-request')->withErrors($validator)->withInput();
		} else {

			$mr = new Musicrequest;
			$mr->empid = Auth::user()->username;
			$mr->empname = Auth::user()->full_name;
			$mr->request = Input::get('request');
			$mr->save();

			Session::set('lastMusicRequest', $mr->toArray());

			Mail::send('emails.tools.musicrequest', array('request' => $mr->toArray()), function($message)
			{
				$message->to('chad@earthboundtrading.com', 'Chad Davis')->subject('Music Request Form');
			});

			return Redirect::to('/tools/music-request-success');
		}
	}

	public function getMusicRequestSuccess()
	{
		return View::make(
			'pages.tools.musicrequest.success'
		);
	}
}
