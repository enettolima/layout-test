<?php

class UsersController extends BaseController
{

    public function getIndex()
    {
        return Redirect::to('/');
    }

    public function getLogin()
    {
        return View::make('pages.users.login');
    }

    public function postSignin()
    {

        if (Auth::attempt(array('username'=>Input::get('username'), 'password'=>Input::get('password')))) {

            // Handle setting of current store
            if (Auth::user()->defaultStore != '') {
                Session::set('storeContext', Auth::user()->defaultStore);
            } elseif (count(Auth::user()->getStores()) > 0) {
                Session::set('storeContext', Auth::user()->getStores()[0]);
            }

            return Redirect::to('/home')
            ->with('message', 'You are now logged in');

        } else {
            return Redirect::to('users/login')
                ->with('loginMessage', 'Invalid Login') ->withInput();
        }

    }

    public function getLogout()
    {
        Auth::logout();
        return Redirect::to('users/login')->with('message', 'You have been logged out.');
    }
}
