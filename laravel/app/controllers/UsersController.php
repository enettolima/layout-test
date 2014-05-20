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

			$response = Requests::post($_ENV['ebt_api_address'] . '/rprousers/auth', array(), $data);

			if ($response->success) {
				$rpResults = json_decode($response->body);

				if ($rpResults->userAuthSuccess && $rpResults->userRetrieved) {
					$goodLogin = true;

					// Here we need to populate the DB accordingly on our side
					// and log in the user

					//$u = User::where('rpro_id', $rpResults->userData->empl_id)->firstOrCreate();

					$u = User::firstOrNew(array('rpro_id' => $rpResults->userData->empl_id));

					if (! $u->id) {
						// Need to save new user
						$u->rpro_user = true;
						$u->username = Input::get('username');
						$u->rpro_id = $rpResults->userData->empl_id;
						$u->save();
					}

					Auth::login($u);
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
