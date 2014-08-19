<?php

class AdminController extends BaseController
{
    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
    }

    public function getIndex()
    {
        return View::make('pages.admin.index');
    }

    public function getUserList()
    {
        $users = User::all();

        return View::make(
            'pages.admin.user.list', array(
                'users' => $users,
            )
        );
    }

    public function getUserEdit()
    {
        $targetUser = Request::segment(3);
        $mainRoles = array();
        $storeRoles = array();

        if (is_numeric($targetUser) && User::find($targetUser)) {
            $user = User::find($targetUser);

            $sr = StoresResolver::getInstance();

            foreach (DB::table('roles')->orderBy('name')->get() /*Role::all()*/ as $role) {
                if (!preg_match('/^Store(\d\d\d)$/i', $role->name, $matches)) {
                    $mainRoles[] = array('name' => $role->name, 'has' => $user->hasRole($role->name));
                } else {
                    if ($matches[1] == '000') {
                        $label = 'Corporate';
                    } else {
                        $store = $sr->getStore($matches[1]);

                        if (isset($store->store_name) && isset($store->city) && isset($store->state)) {
                            $label = $store->store_name . ' - ' . $store->city . ', ' . $store->state; 
                        } else {
                            $label = $matches[1] . " (Can't find store label?)";
                        }
                    }

                    $storeRoles[] = array('name' => $role->name, 'label' => $label, 'has' => $user->hasRole($role->name));
                }
            }
        } else {
            App::abort(403, 'Invalid User');
        }

        return View::make(
            'pages.admin.user.edit', array(
                'user' => $user,
                'mainRoles' => $mainRoles,
                'storeRoles' => $storeRoles
            )
        );
    }

    public function postUserSave()
    {
        $targetUser = Input::get('userId');

        if ($targetUser == 'new') {
            $user = new User;
        } elseif (is_numeric($targetUser) && User::find($targetUser)) {
            $user = User::find($targetUser);
        }

		/*
        $user->fname = Input::get('fname');
        $user->lname = Input::get('lname');
        $user->username = Input::get('username');
        $user->email = Input::get('email');
        if (Input::get('password')) {
            $user->password = Input::get('password');
        }
		*/

        if (! $storeRoles = Input::get('stores')) {
            $storeRoles = array();
        }

        if (! $mainRoles = Input::get('roles')) {
            $mainRoles = array();
        }

        $arrayRoleIds = array();

        foreach (array_merge($storeRoles, $mainRoles) as $role) {
            $arrayRoleIds[] = Role::where('name', '=', $role)->first()->id;
        }

        if ($user->save()) {
            $user->roles()->sync($arrayRoleIds);
        }

        return Redirect::to('admin/user-list')
            ->with('message', 'User updated');
    }

    public function getRoles()
    {
        ob_start();

        echo "<h3>Roles to Permissions</h3>";
        $rolesCollection = Role::all();
        foreach ($rolesCollection as $role) {
            echo "<li><strong>Role:</strong> {$role->name}</li>";

            echo "<ul>";
            if ($role->perms->count() > 0) {
                foreach ($role->perms as $permission) {
                    echo "<li><strong>Permission:</strong> {$permission->name}</li>";
                }
            } else {
                echo "<li><em>Role has no permissions assigned</em></li>";
            }
            echo "</ul>";

        }
        echo '</ul>';


        echo "<h3>Users to Roles</h3>";
        echo "<ul>";

        foreach (User::all() as $user) {
            echo "<li><strong>User: </strong> {$user->username}</li>";
            echo "<ul>";
            foreach ($user->roles as $role) {
                echo "<li>{$role->name}</li>";
            }
            echo "</ul>";
        }
        echo "</ul>";

        $content = ob_get_contents();
        ob_end_clean();
        return View::make('pages.plain')->withContent($content);
    }

    public function getStyles()
    {
        return View::make('pages.dev.styles');
    }
}
