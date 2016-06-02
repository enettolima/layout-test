<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class APITest extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'api:test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command to test the API box to see if everything is working as expected.';
	private $messages;
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		//$this->$messages = array();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$start_date = Date("y-m-d H:i:s");
    $start = microtime(true);
    $this->info("Script Started now -> ".$start_date);
		$this->info("Loading, please wait..");
		//Test API Authentication
		$this->auth();

		//Test Product Image
		$this->pimage();

		//Tests for the document search
		$this->folderSearch();
		$this->documentSearch();
		$this->documentAutoComplete();
		$this->updateDocumentClicks();


		$time_elapsed_secs = microtime(true) - $start;
    $end_date = Date("y-m-d H:i:s");
    $this->messages[] =  "Script started at: ".$start_date." and finished at: ".$end_date." with total time of execution of ".$time_elapsed_secs." seconds.";
		//Extecute at the end to proccess all the messages
		$this->sendMessage();
	}

	/**
	 * Testing the Authentication
	 */
	protected function auth()
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
			$this->saveMessageString("Auth Request");
		}else{
			$this->saveMessageString("Auth Request","error");
		}
	}
	/**
	 * Testing the Image from Retail Pro
	 */
	protected function pimage(){
		$tokenRequestURL = $_ENV['ebt_api_host'] . '/pimg/41388';
		$tokenRequest = Requests::get($tokenRequestURL);
		$this->checkAPIResponse($tokenRequest, "Pimage Request");
	}

	//==============================================================//
	/////////////////// Document Search block ////////////////////////
	//==============================================================//
	/**
	 * Testing the Image from Retail Pro
	 */
	protected function folderSearch(){
		$api = new EBTAPI;
		$results = $api->get('/esdocs/folder-search?id=#');
		$this->checkAPIResponse($api, "Folder Search Request");
	}
	protected function documentSearch(){
		$api = new EBTAPI;
		$results = $api->get('/esdocs/doc-search?page=1&path=&keywords=');
		$this->checkAPIResponse($api, "Document Search Request");
	}
	protected function documentAutoComplete(){
		$api = new EBTAPI;
		$results = $api->get('/esdocs/auto-complete?keyword=inter');
		$this->checkAPIResponse($api, "Document auto-complete Request");
	}
	//This will affect production data
	protected function updateDocumentClicks(){
		$api = new EBTAPI;

		$data['filename'] = 'Markdowns_05-16-16.pdf';
		$data['path'] 		= '/media/web/Markdowns/2016%20Markdowns/Markdowns_05-16-16.pdf';
		$data['id'] 			= '23726';

		//$results = $api->put('/esdocs/update-clicks', $data);
		//$this->checkAPIResponse($api, "Document auto-complete Request");
		$this->saveMessageString("Update Document Clicks", "skip");
	}

	//==============================================================//
	/////////////////// End of Document Search ///////////////////////
	//==============================================================//

	/**
	 * Check response code from the API
	 */
	private function checkAPIResponse($api, $msg){
		if($api->status_code==200){
			$this->saveMessageString($msg);
		}else{
			$this->saveMessageString($msg." - Error: ".$api->status_code,"error");
		}
	}
	/**
	 * Save strings to an array to be processed at the end
	 */
	private function saveMessageString($msg, $type="success"){
		switch ($type) {
			case 'success':
				$this->messages[] = ":large_blue_circle: - ".$msg;
				break;
			case 'error':
				$this->messages[] = ":red_circle: - ".$msg;
				break;
			case 'skip':
				$this->messages[] = ":white_circle: - ".$msg;
				break;
			default:
				$this->messages[] = ":large_blue_circle: - ".$msg;
				break;
		}
	}
	/**
	 * Print to console and send to slack
	 */
	private function sendMessage(){
		$replace = array(':large_blue_circle:', ':red_circle:');
		foreach ($this->messages as $key => $value) {
			if (strpos($value, ':large_blue_circle:') !== false) {
				$newmsg = str_replace(":large_blue_circle:","Success",$value);
			}else if (strpos($value, ':red_circle:') !== false) {
				$newmsg = str_replace(":red_circle:","Failed",$value);
			}else{
				$newmsg = str_replace(":white_circle:","Skipped",$value);
			}
			$this->info($newmsg);
		}
		$msg = implode("\n", $this->messages);
		$tokenRequestURL = $_ENV['ebt_api_host'] . '/slack/slack-notification/app-notifications/API Tester/'.$msg.'/table_tennis_paddle_and_ball';
		$tokenRequest = Requests::get($tokenRequestURL);
	}
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			//array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
