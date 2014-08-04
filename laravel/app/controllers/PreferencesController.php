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
        $user = Auth::user();
        $user->defaultStore = Input::get('defaultStore');
        $user->save();

        return Redirect::to('/preferences')->with('message', 'Settings saved.');
    }
}
