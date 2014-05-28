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
		$goodLogin = false;

		// Try authenticating against the internal database first
		if (Auth::attempt(array('username'=>Input::get('username'), 'password'=>Input::get('password')))) {
			$goodLogin = true;
		} else {
			// Try authenticating against Retail Pro

			$data = array('user' => Input::get('username'), 'password' => Input::get('password'));

            $api = new EBTAPI;

            $rpResults = $api->post('/rprousers/auth', $data);

			if ($rpResults) {

				if ($rpResults->userAuthSuccess && $rpResults->userRetrieved) {
					$goodLogin = true;

					// Here we need to populate the DB accordingly on our side
					// and log in the user

					//$u = User::where('rpro_id', $rpResults->userData->empl_id)->firstOrCreate();

					$u = User::firstOrCreate(array('rpro_id' => $rpResults->userData->empl_id));

                    // Repopulate these every time in case there are changes
                    $u->rpro_user = true;
                    $u->username = $rpResults->userData->empl_name; //Input::get('username');
                    $u->rpro_id = $rpResults->userData->empl_id;
                    $u->full_name = $rpResults->userData->rpro_full_name;
                    $u->last_login = date("Y-m-d H:i:s");
                    $u->save();

					Auth::login($u);

                    // Roles Stuff: if the user has the 'M' flag, give 
                    // them the manager role and the role for the store 
                    // they belong to.
                    //
                    // All others initially have only the 'Guest' role 
                    // (better name for that?)
                    //
                    // Managers can assign the associate role to other 
                    // users for their store. Maybe they can see a list 
                    // of users with their store prefix and can manage 
                    // guest vs associate from there?

				}
			}
		}

		if ($goodLogin) {
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
