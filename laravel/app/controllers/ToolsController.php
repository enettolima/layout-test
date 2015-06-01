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

	public function getMusicRequest()
	{
		return View::make(
			'pages.tools.musicrequest.index'
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
