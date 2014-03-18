<?php

class SettingsController extends BaseController
{

    public function getIndex()
    {
        return View::make('pages.settings.index');
    }

    public function postUpdate()
    {
        $user = Auth::user();
        $user->defaultStore = Input::get('defaultStore');
        $user->save();

        return Redirect::to('/settings')->with('message', 'Settings saved.');
    }
}
