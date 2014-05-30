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

            // MOCK MOCK MOCK
            // $rpResults = $api->post('/rprousers/auth', $data);
            $rpResults = $api->post('/rprousers/mockauth', $data);

			if ($rpResults) {

				if ($rpResults->userAuthSuccess && $rpResults->userRetrieved) {
					$goodLogin = true;

					// Here we need to populate the DB accordingly on our side
					// and log in the user

					//$u = User::where('rpro_id', $rpResults->userData->empl_id)->firstOrCreate();

					$u = User::firstOrCreate(array('rpro_id' => $rpResults->userData->empl_id));

                    // Repopulate these every time in case there are changes
                    $u->rpro_user  = true;
                    $u->username   = $rpResults->userData->empl_name; //Input::get('username');
                    $u->rpro_id    = $rpResults->userData->empl_id;
                    $u->full_name  = $rpResults->userData->rpro_full_name;
                    $u->last_login = date("Y-m-d H:i:s");
                    $u->save();

                    $storeNumber = substr($u->username, 0, 3);

                    if (preg_match('/^(\d\d\d).*$/', $u->username, $matches)) {
                        $homeStoreRole = 'Store' . $matches[1];

                        // Make sure that role exists, create it if not
                        if (! $role = Role::where('name', '=', $homeStoreRole)->first()) {
                            $role = new Role;
                            $role->name = $homeStoreRole;
                            $role->save();
                        }

                        // Make sure the user is assigned that role
                        if (! $u->hasRole($homeStoreRole)) {
                            $u->roles()->sync(array($role->id));
                        }

                        // If the user doesn't already have a default store
                        // assign this new one
                        if (! $u->defaultStore) {
                            $u->defaultStore = $storeNumber;
                            $u->save();
                        }

                    }

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

			return Redirect::to('/home');// ->with('message', 'You are now logged in');
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
