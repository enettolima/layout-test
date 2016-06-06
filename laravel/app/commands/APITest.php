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
	private $messages;//Messages for slack
	private $cmessages;//Messages for the console
	private $total_test;//Total amount of methods tested
	private $total_failed;//Amount of APIs that failed
	private $total_passed;//Total API calls that passed
	private $show;
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		//$this->$messages = array();
		$this->total_test 	= 0;
		$this->total_failed = 0;
		$this->total_passed	= 0;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{

		//sudo php artisan api:test --show=error
		//sudo php artisan api:test --show=all
		$this->show = $this->option('show');

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

		//Tests for the restock
		$this->restockCartStatus();
		$this->restockCartProducts();
		$this->restockAllTempProducts();
		$this->restockProductSearch();
		$this->restockAllCatalogProducts();
		$this->restockAllDCS();
		$this->restockDCSFilter();
		$this->restockProductMinMax();
		$this->restockProductByOrder();
		$this->restockOrderStages();
		$this->restockOrderType();
		$this->restockAddProductToCart();
		$this->restockRemoveProductFromCart();

		//Employees
		$this->employeeGetAllActive();
		$this->employeeLookup();
		//RPro Orders
		$this->rproOrders();
		//RPro User
		$this->rproUserMockAuth();
		$this->rproUserAuth();
		//RPro Products
		$this->rproProductsGetInfo();
		$this->rproProductsUpdateBIN();

		$time_elapsed_secs 	= microtime(true) - $start;
    $end_date 					= Date("y-m-d H:i:s");
    $this->messages[] 	=  "Script started at: ".$start_date." and finished at: ".$end_date." with total time of execution of ".$time_elapsed_secs." seconds.";
		$this->cmessages[] 	=  "Script started at: ".$start_date." and finished at: ".$end_date." with total time of execution of ".$time_elapsed_secs." seconds.";

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
	//==============================================================//
	//////////////////////// Restock block ///////////////////////////
	//==============================================================//
	protected function restockCartStatus(){
		$api = new EBTAPI;
		$results = $api->get('/restock/cart/317/status');
		$this->checkAPIResponse($api, "Restock Cart Status Request");
	}
	protected function restockCartProducts(){
		$api = new EBTAPI;
		$results = $api->get('/restock/cart/317/product');
		$this->checkAPIResponse($api, "Restock Cart Products Request");
	}
	protected function restockAllTempProducts(){
		$api = new EBTAPI;
		$results = $api->get('/restock/all-temp-products');
		$this->checkAPIResponse($api, "Restock All Temp Products Request");
	}
	protected function restockProductSearch(){
		$api = new EBTAPI;
		$results = $api->get('/restock/product/search/317/1/foot/NR');
		$this->checkAPIResponse($api, "Restock Product Search Request");
	}
	protected function restockAllCatalogProducts(){
		$api = new EBTAPI;
		$results = $api->get('/restock/product/all');
		$this->checkAPIResponse($api, "Restock All Catalog Products Request");
	}
	protected function restockAllDCS(){
		$api = new EBTAPI;
		$results = $api->get('/restock/product/dcs');
		$this->checkAPIResponse($api, "Restock All DCS Request");
	}
	protected function restockDCSFilter(){
		$api = new EBTAPI;
		$results = $api->get('/restock/product/dcs-filter/301');
		$this->checkAPIResponse($api, "Restock DCS Filter Request");
	}
	protected function restockProductMinMax(){
		$api = new EBTAPI;
		$results = $api->get('/restock/product/store-min-max');
		$this->checkAPIResponse($api, "Restock Product Min Max Request");
	}
	protected function restockProductByOrder(){
		$api = new EBTAPI;
		$results = $api->get('/restock/product-by-order/41388');
		$this->checkAPIResponse($api, "Restock Product By Order Request");
	}
	protected function restockOrderByStore(){
		$api = new EBTAPI;
		$results = $api->get('/restock/order/470');
		$this->checkAPIResponse($api, "Restock Order By Store Request");
	}
	protected function restockOrderStages(){
		$api = new EBTAPI;
		$results = $api->get('/restock/order-stages');
		$this->checkAPIResponse($api, "Restock Order Stage Request");
	}
	protected function restockOrderType(){
		$api = new EBTAPI;
		$results = $api->get('/restock/order-type');
		$this->checkAPIResponse($api, "Restock Order Type Request");
	}
	//This will affect production data
	protected function restockAddProductToCart(){
		$api = new EBTAPI;
		$data['product_id'] = 313;
		$data['quantity'] 	= 5;
		//$results = $api->post('/restock/cart/150765/product', $data);
		//$this->checkAPIResponse($api, "Restock Add Product to Cart Request");
		$this->saveMessageString("Restock Add Product to Cart Request", "skip");
	}
	//This will affect production data
	protected function restockRemoveProductFromCart(){
		$api = new EBTAPI;
		//$results = $api->delete('/restock/cart/150765/product/313');
		//$this->checkAPIResponse($api, "Restock Add Product to Cart Request");
		$this->saveMessageString("Restock Remove Product From Cart Request", "skip");
	}
	//==============================================================//
	//////////////////// End of Restock block ///////////////////////
	//==============================================================//
	//==============================================================//
	/////////////////////// Employee block ///////////////////////////
	//==============================================================//
	protected function employeeGetAllActive(){
		$api = new EBTAPI;
		$results = $api->get('/rproemployees/active');
		$this->checkAPIResponse($api, "Employee Get All Request");
	}
	protected function employeeLookup(){
		$api = new EBTAPI;
		$results = $api->get('/rproemployees/lookup-by-emp-num/10022');
		$this->checkAPIResponse($api, "Employee Lookup Request");
	}
	//==============================================================//
	//////////////////// End of Employee block ///////////////////////
	//==============================================================//
	//==============================================================//
	/////////////////////// RProOrder block //////////////////////////
	//==============================================================//
	protected function rproOrders(){
		$api = new EBTAPI;
		$results = $api->get('/rproorders/order/100039716');
		$this->checkAPIResponse($api, "RProOrder by ID Request");
	}
	//==============================================================//
	//////////////////// End of RProOrder block //////////////////////
	//==============================================================//
	//==============================================================//
	/////////////////////// RProUser block ///////////////////////////
	//==============================================================//
	protected function rproUserMockAuth(){
		$api = new EBTAPI;
		$data['user'] = '000NN';
		$data['password'] 	= '2016';
		$results = $api->post('/rprousers/mockauth', $data);
		$this->checkAPIResponse($api, "RProUser Mock Auth Request");
	}
	protected function rproUserAuth(){
		$api = new EBTAPI;
		$data['user'] = '000NN';
		$data['password'] 	= '2016';
		$results = $api->post('/rprousers/auth', $data);
		$this->checkAPIResponse($api, "RProUser Auth Request");
	}
	//==============================================================//
	//////////////////// End of RProUser block ///////////////////////
	//==============================================================//
	//==============================================================//
	//////////////////// RProd Products block ////////////////////////
	//==============================================================//
	protected function rproProductsGetInfo(){
		$api = new EBTAPI;
		$results = $api->get('/rproproducts/product-info/41388');
		$this->checkAPIResponse($api, "RProProducts Get Info Request");
	}
	//This will affect production data
	protected function rproProductsUpdateBIN(){
		$api = new EBTAPI;
		$data['bin_no'] 	= 'AC11B06';
		$data['item_sid'] = '-1303818204129849348';
		//$results = $api->post('/rproproducts/update-bin', $data);
		//$this->checkAPIResponse($api, "RPro Products Update BIN Request");
		$this->saveMessageString("RPro Products Update BIN Request", "skip");
	}
	//This will affect production data
	protected function rproProductsAddSecondaryBIN(){
		$api = new EBTAPI;
		$data['bin_number'] = 'AC11B06';
		$data['item_sid'] 	= '-1303818204129849348';
		$data['item_no'] 		= '41388';
		$data['old_bin'] 		= '';
		$data['quantity'] 	= 12;
		$data['description']= 'This is for tests only';
		$data['employee_id']= '000NN';

		//$results = $api->post('/rproproducts/add-secondary-location', $data);
		//$this->checkAPIResponse($api, "RPro Products Add Secondary BIN Request");
		$this->saveMessageString("RPro Products Add Secondary BIN", "skip");
	}
	//This will affect production data
	protected function rproProductsUpdateSecondaryBIN(){
		$api = new EBTAPI;

		$data['bin_number'] = 'AC11B07';
		$data['bin_no'] 		= 'AC11B07';
		$data['item_sid'] 	= '-1303818204129849348';
		$data['item_no'] 		= '41388';
		$data['old_bin'] 		= 'AC11B06';
		$data['quantity'] 	= 13;
		$data['qty_old'] 		= 12;
		$data['qty_new'] 		= 13;
		$data['description']= 'This is for tests only';
		$data['employee_id']= '000NN';
		$data['bin_id']			= '16998';

		//$results = $api->post('/rproproducts/update-secondary-location', $data);
		//$this->checkAPIResponse($api, "RPro Products Update Secondary BIN Request");
		$this->saveMessageString("RPro Products Update Secondary BIN Request", "skip");
	}
	//This will affect production data
	protected function rproProductsDeleteSecondaryBIN(){
		$api = new EBTAPI;
		$data['bin_number'] = 'AC11B07';
		$data['bin_no'] 		= 'AC11B07';
		$data['item_sid'] 	= '-1303818204129849348';
		$data['item_no'] 		= '41388';
		$data['old_bin'] 		= 'AC11B06';
		$data['quantity'] 	= 13;
		$data['qty_old'] 		= 12;
		$data['qty_new'] 		= 13;
		$data['description']= 'This is for tests only';
		$data['employee_id']= '000NN';
		$data['bin_id']			= '16998';
		//$results = $api->post('/rproproducts/delete-secondary-location', $data);
		//$this->checkAPIResponse($api, "RPro Products Delete Secondary BIN Request");
		$this->saveMessageString("RPro Products Delete Secondary BIN Request", "skip");
	}
	//==============================================================//
	/////////////////// End RProd Products block /////////////////////
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
		if(!$this->show || $this->show==""){
			$this->show = 'all';
		}
		$this->total_test++;
		switch ($type) {
			case 'success':
				if($this->show == 'all'){
					$this->messages[] = ":large_blue_circle: - ".$msg;
				}
				$this->cmessages[] 	= "Success - ".$msg;
				$this->total_passed++;
				break;
			case 'error':
				$this->messages[] 	= ":red_circle: - ".$msg;
				$this->cmessages[] 	= "Error - ".$msg;
				$this->total_failed++;
				break;
			case 'skip':
				if($this->show == 'all'){
					$this->messages[] = ":white_circle: - ".$msg;
				}
				$this->cmessages[] 	= "Skip - ".$msg;
				$this->total_passed++;
				break;
			default:
				if($this->show == 'all'){
					$this->messages[] = ":large_blue_circle: - ".$msg;
				}
				$this->cmessages[] 	= "Success - ".$msg;
				$this->total_passed++;
				break;
		}
	}
	/**
	 * Print to console and send to slack
	 */
	private function sendMessage(){
		$this->messages[] 	=  "Total of Requests: ".$this->total_test.": ".$this->total_passed." Success | ".$this->total_failed." Errors";
		$this->cmessages[] 	=  "Total of Requests: ".$this->total_test.": ".$this->total_passed." Success | ".$this->total_failed." Errors";
		//$replace = array(':large_blue_circle:', ':red_circle:');
		foreach ($this->cmessages as $key => $value) {
			/*if (strpos($value, ':large_blue_circle:') !== false) {
				$newmsg = str_replace(":large_blue_circle:","Success",$value);
			}else if (strpos($value, ':red_circle:') !== false) {
				$newmsg = str_replace(":red_circle:","Failed",$value);
			}else{
				$newmsg = str_replace(":white_circle:","Skipped",$value);
			}*/
			$this->info($value);
		}
		$msg = implode("\n", $this->messages);
		$tokenRequestURL = $_ENV['ebt_api_host'] . '/slack/slack-notification/app-notifications/API Tester/'.$msg.'/table_tennis_paddle_and_ball';
		Log::info($_ENV['ebt_api_host'] . '/slack/slack-notification/app-notifications/API Tester/'.$msg.'/table_tennis_paddle_and_ball');
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
			//array('message', InputArgument::REQUIRED, 'Set is all messages will be sent to slack.'),
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
			array('show', 'msg', InputOption::VALUE_OPTIONAL, 'Show all/error messages on slack notification.', null),
		);
	}

}
