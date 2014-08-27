<?php

class PreferencesController extends BaseController
{
    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
    }

    public function getIndex()
    {
        return View::make('pages.preferences.index');
    }

    public function postUpdate()
    {

        $rules = array(
            'preferredEmail' => 'email',
        );
 
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {

            return Redirect::to('/preferences')->withErrors($validator)->withInput();

        } else {

            $user = Auth::user();
            $user->defaultStore = Input::get('defaultStore');
            $user->preferred_email = Input::get('preferredEmail');
            $user->save();

            return Redirect::to('/preferences')->with('message', 'Settings saved.');
        }
    }
}
