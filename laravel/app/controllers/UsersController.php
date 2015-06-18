<?php

class UsersController extends BaseController
{

    public $errorCode = null;

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
     *
     * Return: string in 'Manager', 'Associate Manager', 'Associate', 'Guest'
     */
    protected function getUserLevel($rpResults)
    {
        $returnval = false;

        // Look for "M" in Retail Pro column 'empl_no2', signifying manager
        if (preg_match('/^m$/i', $rpResults->userData->empl_no2)) {
            $returnval = 'Manager';
        } else {
            // Assign various levels based on group membership
            // These need to be ordered as "most powerful first"

            $rpGroups = array();

            if (! property_exists($rpResults->userData, 'groups')) {
                $this->errorCode = 'E101'; // Couldn't find any RP Groups.
                return $returnval;
            }

            // var_dump($rpResults->userData);
            // exit;

            foreach ($rpResults->userData->groups as $group) {
                $rpGroups[] = $group->user_grp_name;
            }

            // The following defines the Valid groups from Retail Pro, in the order of most privileged to 
            // least. If the user is a member of one of these groups in Retail Pro, they will be processed
            // according to the first one that matches.
            $rpGroupPermOrder = array ('EBTPASSPORT_DM', 'EBTPASSPORT_AMAN', 'EBTPASSPORT_ASSOCIATE', 'EBTPASSPORT_GUEST');

            $rpGroup = null;

            foreach ($rpGroupPermOrder as $rpGroupPerm) {
                if (in_array($rpGroupPerm, $rpGroups)) {
                    $rpGroup = $rpGroupPerm;
                    break;
                }
            }

            switch ($rpGroup) {
                case 'EBTPASSPORT_DM':
                    $returnval = 'District Manager';
                    break;
                case 'EBTPASSPORT_AMAN':
                    $returnval = 'Assistant Manager';
                    break;
                case 'EBTPASSPORT_ASSOCIATE':
                    $returnval = 'Associate';
                    break;
                case 'EBTPASSPORT_GUEST':
                    $returnval = 'Guest';
                    break;
            }
        }

        return $returnval;
    }

    public function postSignin()
    {
		$goodLogin = false;

		// Try authenticating against the internal database first
		if (Auth::attempt(array('username'=>Input::get('username'), 'password'=>Input::get('password')))) {
			$goodLogin = true;
		} else {
            // Try authenticating against Retail Pro. We only accept RPro users with the form NNNwhatever 

            if (preg_match('/^(\d\d\d).*$/', Input::get('username'), $matches)) {

                $storeNumber = $matches[1];

                try {
                    StoresLookup::where('code', $storeNumber)->firstOrFail();
                    $validStore = true;
                } catch(Exception $e) {
                    if ($storeNumber == '000') {
                        $validStore = true;
                    } else {
                        $validStore = false;
                    }
                }

                if ($validStore){

                    $data = array('user' => Input::get('username'), 'password' => Input::get('password'));

                    $api = new EBTAPI;

                    // Todo: Set this up in some sort of key-based fashion? 
                    // Reckless.
                    if (isset($_ENV['mock_rpro_auth']) && ($_ENV['mock_rpro_auth'] === TRUE) && ($_SERVER['HTTP_HOST'] !== 'ebtpassport.com')) {
                        $rpResults = $api->post('/rprousers/mockauth', $data);
                    } else {
                        $rpResults = $api->post('/rprousers/auth', $data);
                    }

                    if ($rpResults) {


                        if (($rpResults->userAuthSuccess && $rpResults->userRetrieved) && $userLevel = $this->getUserLevel($rpResults)) {

                            $goodLogin = true;

                            // First, see if anything comes back for rpro_id + username in my user
                            // If it *does*, use that user like normal
                            //
                            // If it *does not* before creating the new user, make sure there's not
                            // someone in the user table already with that username (empl_name)

                            $u = User::where('rpro_id', $rpResults->userData->empl_id)->where('username', $rpResults->userData->empl_name)->first();

                            if (! $u) {
                                // Before Creating user, check to see if there's an existing user in the table with this username.
                                if ($oldUser = User::where('username', $rpResults->userData->empl_name)->first()){
                                    $oldUser->roles()->sync(array());
                                    $oldUser->delete();
                                }

                                $u = new User;
                                $u->rpro_id = $rpResults->userData->empl_id;
                                $u->username = $rpResults->userData->empl_name;
                            }

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

                            $homeStoreRoleName = 'Store' . $storeNumber;

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

                            /*
                             * HANDLE ASSIGNATION OF USER TO THEIR 'LEVEL' ROLE
                             *
                             * They can only have one out of validRoles, so we
                             * verify they have the one they need and none they don't.
                             *
                             * Todo: This is currently bound using the label instead of the Group's ID
                             */

                            $validRoles = array('District Manager', 'Manager', 'Assistant Manager', 'Associate', 'Guest');

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


                            /*
                             * HANDLE ASSIGNATION OF STORE ROLES TO DMs and RMs
                             * 
                             * We also need to assign all the stores managed by any DMs or RMs
                             *
                             * Currently in Retail Pro we are only assigning the group
                             * 'District Manager' to users who are in the RetailPro Group 'PASSPORT_DM'.
                             *
                             * This fails to account for the difference between an Earthbound 'District
                             * Manager' and an Earthbound 'Regional Manager', so this is a bit messy and
                             * will need to be changed when we implement the 'Regional Manager' concept
                             */


                            if ($u->hasRole('District Manager')) {

                                // Create array of existing roles
                                $userRoles = array();

                                foreach ($u->roles()->get() as $role) {
                                    $userRoles[] = $role->id;
                                }

                                // Get roles from database
                                //TODO: move to API. Also, THIS REALLY SUCKS.
                                $sql = "select [Code #] as store from PASSPORT_STORES_DM_RM where RM_RP_LOGIN = '{$u->username}' or DM_RP_LOGIN = '{$u->username}'";
                                $managerStoresRes = DB::connection('sqlsrv_ebt')->select($sql);

                                foreach ($managerStoresRes as $result) {

                                    $targetStore = 'Store'.$result->store;

                                    // Load up actual Role object for store or create one if it doesn't 
                                    // already exist
                                    if (! $storeRole = Role::where('name', '=', $targetStore)->first()) {
                                        $storeRole = new Role;
                                        $storeRole->name = $targetStore;
                                        $storeRole->save();
                                    }

                                    // Add this role to our array of roles this user should have.
                                    // I assume this might create duplicates but that doesn't effect
                                    // the sync method...
                                    $userRoles[] = $storeRole->id;

                                }

                                // Sync (assign) the roles to the user
                                $u->roles()->sync($userRoles);
                            }


                            /*
                             * HANDLE SYNCING OF "EBTPERM" ROLES.
                             *
                             * There are groups in RetailPro named "EBTPERM_???????"
                             *
                             * For each one of those returned in the user's groups listing, we want
                             * to create (if neccessary) the role and assign to the user.
                             */

                            $userRoles = array();

                            foreach ($u->roles()->get() as $role) {
                                $userRoles[] = $role->id;
                            }

                            foreach ($rpResults->userData->groups as $group) {
                                if(preg_match('/^EBTPERM_(\S+)$/', $group->user_grp_name)) {
                                    if (! $permRole = Role::where('name', '=', $group->user_grp_name)->first()) {
                                        $permRole = new Role;
                                        $permRole->name = $group->user_grp_name;
                                        $permRole->save();
                                    }
                                    $userRoles[] = $permRole->id;
                                }
                            }

                            $u->roles()->sync($userRoles);

                            UserLog::logSuccess($u->username);
                            // Destroy any previous session first...
                            Session::flush();

                            Auth::login($u);
                        }
                    }
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

            $loginFailureMessage = 'Invalid Login';

            if (! $validStore) {
                UserLog::logFailure(Input::get('username') . ' (BAD STORE) ');
            } elseif ($this->errorCode) {
                UserLog::logFailure(Input::get('username') . " ({$this->errorCode}) ");
                $loginFailureMessage = 'Invalid Login (' . $this->errorCode . ')';
            } else {
                UserLog::logFailure(Input::get('username'));
            }

			return Redirect::to('users/login')
				->with('loginMessage', $loginFailureMessage) ->withInput();
		}
    }

    public function getLogout()
    {
        Auth::logout();
        return Redirect::to('users/login')->with('message', 'You have been logged out.');
    }
}
