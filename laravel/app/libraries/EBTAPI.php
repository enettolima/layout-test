<?php

class EBTAPI
{
	protected $token = null;
	protected $debug = true;

	public function __construct()
	{

		if (! isset ($_ENV['ebt_api_host'])) {
			throw new Exception ("\$_ENV['ebt_api_host'] not set");
		}

		if (! isset ($_ENV['ebt_api_uri'])) {
			throw new Exception ("\$_ENV['ebt_api_uri'] not set");
		}

		if (! isset ($_ENV['ebt_api_username'])) {
			throw new Exception ("\$_ENV['ebt_api_username'] not set");
		}

		if (! isset ($_ENV['ebt_api_password'])) {
			throw new Exception ("\$_ENV['ebt_api_password] not set");
		}


		if (! $this->token) {
			$this->log(__METHOD__ . ' $this->token not set');

			$this->token = Session::get('api_token');

			if (! $this->token) {
				$this->log(__METHOD__ . ' $this->token not populated from session ');
				$this->getToken();
			} else {
				$this->log(__METHOD__ . ' $this->token retreived from session');
			}
		}
	}

	protected function log($string)
	{
		if ($this->debug) {
			Log::info($string);
		}
	}

	protected function getToken()
	{
		$this->log(__METHOD__);

		$tokenRequestURL = $_ENV['ebt_api_host'] . '/auth';

		$tokenRequest = Requests::post(
			$tokenRequestURL, 
			array(), 
			array(
				'username' => $_ENV['ebt_api_username'], 
				'password' => $_ENV['ebt_api_password']
			)
		); 

		if ($tokenRequest->success && $token = json_decode($tokenRequest->body)->token) {

			$this->log(__METHOD__ . 'new token received, setting session and local');

			Session::set('api_token', $token);

			$this->token = $token;

		} else {
			$msg = "Could not get API token.";
			if ($error = json_decode($tokenRequest->body)->error) {
				$msg .= " Error from svc: $error";
			}

			throw new Exception($msg);
		}
	}

	protected function resetToken()
	{
		$this->log(__METHOD__);
		Session::remove('api_token');
		$this->getToken();
	}

	public function get($resource)
	{
		$this->log(__METHOD__);
		$returnval = false;

		$response = Requests::get($_ENV['ebt_api_host'] . $_ENV['ebt_api_uri'] . $resource, array('X-Auth-Token' => $this->token));

		if ($response->success) {
			$this->log(__METHOD__ . ' success');
			$returnval = json_decode($response->body);
		} elseif($response->status_code === 401) {
			$this->log(__METHOD__ . ' 401 Unauthorized');
			$this->resetToken();
			$returnval = $this->get($resource);
		}

		return $returnval;
	}

	public function post($resource, $vals)
	{
		$returnval = false;

		$response = Requests::post($_ENV['ebt_api_host'] . $_ENV['ebt_api_uri'] . $resource, array('X-Auth-Token' => $this->token), $vals);

		if ($response->success) {
			$returnval = json_decode($response->body);
		} elseif($response->status_code === 401) {
			$this->resetToken();
			$this->get($resource);
		}

		return $returnval;
	}
}
