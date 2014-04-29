<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
use Zizaco\Entrust\HasRole;

class User extends Eloquent implements UserInterface, RemindableInterface {

    use HasRole;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password');



	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}

    /**
     * The pattern by which we determin whether a role
     * is a Store Role or not
     */
    protected $storeRegex = '/^store(\d+)$/i';

    /**
     * Filtered collection of 'StoreNNN' roles assigned
     *
     * @return object Eloquent Collection
     */
    public function getStoreRoles()
    {
        $storeRoles = $this->roles->filter(function($role)
        {
            return preg_match($this->storeRegex, $role->name);
        });

        return $storeRoles;
    }

    /**
     * Filtered collection of non-'StoreNNN' (or 'standard') roles assigned
     *
     * @return object Eloquent Collection
     */
    public function getNonStoreRoles()
    {
        $roles = $this->roles->filter(function($role)
        {
            return ! preg_match($this->storeRegex, $role->name);
        });

        return $roles;
    }

    /**
     * Get the stores which this user belongs to, based on the 
     * existence of 'StoreNNN' roles assigned.
     *
     * @return array
     */
    public function getStores()
    {
        $returnval = array();

        foreach ($this->roles as $role) {
            if (preg_match($this->storeRegex, $role->name, $matches)) {
                $returnval[] = $matches[1];
            }
        }

        return $returnval;
    }

    /**
     * Zizaco's roles w/o the 'StoreNNN' roles. 
     *
     * return $
    public function getRoles()
    {
    */

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}
