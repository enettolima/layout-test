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

    /*
     * Todo: among other issues here, the biggest is the fact that this
     * is currently bound via a label instead of an id.
     */
    protected function getUserLevel($rpResults)
    {
        $isGuest = false;
        $isAssociate = false;
        $isManager = false;

        if (preg_match('/^m$/i', $rpResults->userData->empl_no2)) {
            $isManager = true;
        } else {
            foreach ($rpResults->userData->groups as $group) {
                switch ($group->user_grp_name) {
                    case 'EBTPASSPORT_GUEST':
                        $isGuest = true;
                        break;
                    case 'EBTPASSPORT_ASSOCIATE':
                        $isAssociate = true;
                        break;
                }
            }
        }

        /*
         * NOTE: These Directly Correspond to the Role Names
         */
        if ($isManager) {
            return 'Manager';
        } elseif ($isAssociate) {
            return 'Associate';
        } elseif ($isGuest) {
            return 'Guest';
        } else {
            return false;
        }
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

				if (($rpResults->userAuthSuccess && $rpResults->userRetrieved) && $userLevel = $this->getUserLevel($rpResults)) {

					$goodLogin = true;

					$u = User::firstOrCreate(array('rpro_id' => $rpResults->userData->empl_id));

                    // Repopulate these every time in case there are changes
                    $u->rpro_user  = true;
                    $u->username   = $rpResults->userData->empl_name; //Input::get('username');
                    $u->rpro_id    = $rpResults->userData->empl_id;
                    $u->full_name  = $rpResults->userData->rpro_full_name;
                    $u->last_login = date("Y-m-d H:i:s");

                    $u->save();

                    /*
                     * HANDLE ASSIGNATION OF USER TO THEIR STORE ROLE
                     */
                    $storeNumber = substr($u->username, 0, 3);

                    if (preg_match('/^(\d\d\d).*$/', $u->username, $matches)) {
                        $homeStoreRoleName = 'Store' . $matches[1];

                        // Make sure that role exists, create it if not
                        if (! $homeStoreRole = Role::where('name', '=', $homeStoreRoleName)->first()) {
                            $homeStoreRole = new Role;
                            $homeStoreRole->name = $homeStoreRoleName;
                            $homeStoreRole->save();
                        }

                        // Make sure the user is assigned that role
                        if (! $u->hasRole($homeStoreRole)) {


                            $userRoles = array();

                            foreach ($u->roles()->get() as $role) {
                                $userRoles[] = $role->id;
                            }

                            $userRoles[] = $homeStoreRole->id;


                            $u->roles()->sync($userRoles);
                        }

                        // If the user doesn't already have a default store
                        // assign this new one
                        if (! $u->defaultStore) {
                            $u->defaultStore = $storeNumber;
                            $u->save();
                        }

                    }

                    /*
                     * HANDLE ASSIGNATION OF USER TO THEIR 'LEVEL' ROLE
                     *
                     * They can only have one out of validRoles, so we
                     * verify they have the one they need and none they don't.
                     *
                     * Todo: This is currently bound using the label instead of the Group's ID
                     */

                    $validRoles = array('Manager', 'Associate', 'Guest');

                    $userLevelRole = Role::where('name', '=', $userLevel)->firstOrFail();

                    // These are the roles we want to make sure the user doesn't
                    $removeRoles = array_diff($validRoles, array($userLevel));

                    $removeRoleIds = array();
                    foreach ($removeRoles as $removeRole) {
                        $removeRoleObj = Role::where('name', '=', $removeRole)->firstOrFail();
                        $removeRoleIds[] = $removeRoleObj->id;
                    }

                    // These are what we're going to set the roles to
                    $userRoles = array();

                    foreach ($u->roles()->get() as $role) {
                        if (! in_array($role->id, $removeRoleIds)) {
                            $userRoles[] = $role->id;
                        }
                    }

                    if (! in_array($userLevelRole->id, $userRoles)) {
                        $userRoles[] = $userLevelRole->id;
                    }

                    $userRoles[] = $userLevelRole->id;

                    $u->roles()->sync($userRoles);

                    // $managerRole = Role::where('name', '=', 'Manager')->first();

                    /*
                    // Manage "Manager" role sync
                    if (preg_match('/^m$/i', $rpResults->userData->empl_no2)) {
                        // This user should have "Manager"

                        if (! $u->hasRole($managerRole)) {

                            $userRoles = array();

                            foreach ($u->roles()->get() as $role) {
                                $userRoles[] = $role->id;
                            }

                            $userRoles[] = $managerRole->id;


                            $u->roles()->sync($userRoles);
                        }

                    } else {
                        // This user should not have "Manager". This is probably a wonky way to do this
                        // but I'm not sure how to pluck one role from a user.

                        if ($u->hasRole($managerRole)) {
                            $userRoles = array();
                            foreach ($u->roles()->get() as $role) {
                                if ($role->id !== $managerRole->id) {
                                    $userRoles[] = $role->id;
                                }
                            }

                            $u->roles()->sync($userRoles);
                        }
                    }
                    */

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
