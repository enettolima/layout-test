<?php

class EBTAPI
{
	protected $token = null;
	public $status_code = 0;

	public function __construct($allowTokenFromSession = TRUE)
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
			// Clog::log(__METHOD__ . ' $this->token not set');

            if ($allowTokenFromSession) {
                $this->token = Session::get('api_token');
            }

			if (! $this->token) {
				// Clog::log(__METHOD__ . ' $this->token not populated from session ');
				$this->getToken();
			} else {
				// Clog::log(__METHOD__ . ' $this->token retreived from session');
			}
		}
	}

	protected function getToken()
	{
		$tokenRequestURL = $_ENV['ebt_api_host'] . '/auth';

		$tokenRequest = Requests::post(
			$tokenRequestURL,
			array(),
			array(
				'username' => $_ENV['ebt_api_username'],
				'password' => $_ENV['ebt_api_password']
            ),
            array('verify' => false)
		);

		if ($tokenRequest->success && $token = json_decode($tokenRequest->body)->token) {

			// Clog::log(__METHOD__ . 'new token received, setting session and local');

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
		// Clog::log(__METHOD__);
		Session::remove('api_token');
		$this->getToken();
	}

	public function get($resource)
	{
		// Clog::log(__METHOD__);
		$returnval = false;

		$response = Requests::get($_ENV['ebt_api_host'] . $_ENV['ebt_api_uri'] . $resource, array('X-Auth-Token' => $this->token), array('verify' => false));

		if ($response->success) {
			// Clog::log(__METHOD__ . ' success');
			$returnval = json_decode($response->body);
		} elseif($response->status_code === 401) {
			// Clog::log(__METHOD__ . ' 401 Unauthorized');
			$this->resetToken();
			$returnval = $this->get($resource);
		}
		$this->status_code = $response->status_code;
		return $returnval;
	}

	public function post($resource, $vals)
	{
		// Clog::log(__METHOD__);

		$returnval = false;

		$url = $_ENV['ebt_api_host'] . $_ENV['ebt_api_uri'] . $resource;
		$headers = array('X-Auth-Token' => $this->token);

		// Clog::log($url);
		// Clog::log($headers);
		// Clog::log($vals);


		$response = Requests::post($url, $headers, $vals, array('verify' => false));

		// Clog::log($response);

		if ($response->success) {
			// Clog::log(1);
			$returnval = json_decode($response->body);
		} elseif($response->status_code === 401) {
			// Clog::log(2);
			$this->resetToken();
			$returnval = $this->post($resource, $vals);
		}
		$this->status_code = $response->status_code;
		return $returnval;
	}

	public function put($resource, $vals)
	{
		// Clog::log(__METHOD__);

		$returnval = false;

		$url = $_ENV['ebt_api_host'] . $_ENV['ebt_api_uri'] . $resource;
		$headers = array('X-Auth-Token' => $this->token);

		// Clog::log($url);
		// Clog::log($headers);
		// Clog::log($vals);


		$response = Requests::put($url, $headers, $vals, array('verify' => false));

		// Clog::log($response);

		if ($response->success) {
			// Clog::log(1);
			$returnval = json_decode($response->body);
		} elseif($response->status_code === 401) {
			// Clog::log(2);
			$this->resetToken();
			$returnval = $this->post($resource, $vals);
		}
		$this->status_code = $response->status_code;
		return $returnval;
	}

	public function delete($resource)
	{
		// Clog::log(__METHOD__);

		$returnval = false;

		$url = $_ENV['ebt_api_host'] . $_ENV['ebt_api_uri'] . $resource;
		$headers = array('X-Auth-Token' => $this->token);

		// Clog::log($url);
		// Clog::log($headers);
		// Clog::log($vals);


		$response = Requests::delete($url, $headers, array('verify' => false));

		// Clog::log($response);

		if ($response->success) {
			// Clog::log(1);
			$returnval = json_decode($response->body);
		} elseif($response->status_code === 401) {
			// Clog::log(2);
			$this->resetToken();
			$returnval = $this->post($resource, $vals);
		}
		$this->status_code = $response->status_code;
		return $returnval;
	}
}
