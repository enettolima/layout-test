<?php

class EBTAPI
{
	protected $token = null;
	public $status_code = 0;
	private $show_log = false;//Show request and response
	private $url;
	private $headers;
	private $counter = 0;

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

            if ($allowTokenFromSession) {
                $this->token = Session::get('api_token');
            }

			if (! $this->token) {
				$this->getToken();
			}
		}
		$this->counter = 1;
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
		Session::remove('api_token');
		$this->getToken();
	}

	public function get($resource)
	{
		$this->buildParams($resource);

		$response = Requests::get($this->url, $this->headers, array('verify' => false));
		$this->showLog("GET",array(), $response);

		return $this->processResponse($response, "get", null);
	}

	public function post($resource, $vals)
	{
		$this->buildParams($resource);

		$response = Requests::post($this->url, $this->headers, $vals, array('verify' => false, 'timeout' => 300));

		$this->showLog("POST",$vals, $response);

		return $this->processResponse($response, "post", $resource, $vals);
	}

	public function put($resource, $vals)
	{
		$this->buildParams($resource);

		$response = Requests::put($this->url, $this->headers, $vals, array('verify' => false));

		$this->showLog("PUT",$vals, $response);

		return $this->processResponse($response, "post", null);
	}

	public function delete($resource)
	{
		$this->buildParams($resource);

		$response = Requests::delete($this->url, $this->headers, array('verify' => false));

		$this->showLog("DELETE",array(), $response);

		return $this->processResponse($response, "post", null);
	}

	private function buildParams($resource){
		$this->url = $_ENV['ebt_api_host'] . $_ENV['ebt_api_uri'] . $resource;
		$this->headers = array('X-Auth-Token' => $this->token);
	}

	private function showLog($method, $vals, $response){
		if($this->show_log){
			Log::info($method." Request to ".$this->url,array('Headers' => $this->headers, 'vars'=> $vals));
			Log::info($method." Response -- ".$response->status_code,array('Response' => json_decode($response->body)));
		}
	}

	private function processResponse($response, $request_type, $resource, $vals=""){
		$returnval = false;
		$this->status_code = $response->status_code;
		if($response->status_code === 401 && $this->counter <3){
			$this->resetToken();
			switch ($request_type) {
				case 'get':
					$returnval = $this->get($resource);
					break;
				case 'post':
					$returnval = $this->post($resource, $vals);
					break;
				case 'put':
					$returnval = $this->put($resource, $vals);
					break;
				case 'delete':
					$returnval = $this->delete($resource);
					break;
				default:
					# code...
					break;
			}
			//Preventing Infinite loop in case the API always returns 401
			$this->counter = $this->counter + 1;
			//$returnval = $this->post($resource, $vals);
		}else{

			$returnval = json_decode($response->body);

		}
		return $returnval;
	}
}
