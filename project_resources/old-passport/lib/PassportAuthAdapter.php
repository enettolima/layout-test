<?php
class PassportAuthAdapter implements Zend_Auth_Adapter_Interface
{
	private $username;
	private $passhash;

    public function __construct($username, $passhash) {
    	$this->username = $username;
    	$this->passhash = $passhash;
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate() {
    	$user = User::findByUsername($this->username);
    	if (empty($user))
    		return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null, array('User does not exist'));
    	
    	if ($user->passhash != $this->passhash)
    		return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null, array('Incorrect password used'));
    		
    	return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $user, array());
    }
}