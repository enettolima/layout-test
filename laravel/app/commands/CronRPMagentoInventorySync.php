<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CronRPMagentoInventorySync extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'cron:rp-magento-inventory-sync';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync Magento Inventory with Retail Pro Inventory';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

    public function fire()
    {
        try {

            /*
            $req = Requests::post(
                'http://dev.ebtpassport.com:9200/mydocs/doc/_search',
                array(),
                $json
            );

            return $req->body;
             */

            $headers = null;
            $options = null;

            $request = Requests::get(
                'http://shop.earthboundtrading.com/ebtutil/inventory/getproducts.php'//,
                //$headers,
                //$options
            );

            $products = array();

            if ($request->success) {
                $api = new EBTAPI;
                foreach (json_decode($request->body)->data as $sid) {
                    $this->info($sid);
                    //100003027
                    // To be replaced with something real that gets the Inventory Amt..
                    $productRequest = $api->get("/rproorders/order/100003027");
                    $products[] = array('sid' => $sid, 'rpcount' => 10);
                }
            }


        } catch(Exception $e) {
            echo $e->getMessage();
            exit(1);
        }
    }

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function examplefire()
	{
        try {

            $api = new EBTAPI;
            $employees = $api->get('/rproemployees/active');

            $activeEmployeesLookup = array();
            $employeesChanged = array();
            $newEmployees = array();
            $employeesRemoved = array();

            foreach ($employees as $key=>$employee) {

                $activeEmployeesLookup[] = $employee->empl_id;

                if ($el = EmployeesLookup::where('empl_id', $employee->empl_id)->first()) {

                    $elPrev = clone $el; // Stash a copy of the original for the report

                    $updateFlag = false;

                    if ($el->active != $employee->active) {
                        $el->active = $employee->active;
                        $updateFlag = true;
                    }

                    if ($el->description != $employee->description) {
                        $el->description = $employee->description;
                        $updateFlag = true;
                    }

                    if ($el->empl_name != $employee->empl_name) {
                        $el->empl_name = $employee->empl_name;
                        $updateFlag = true;
                    }

                    if ($el->empl_no1 != $employee->empl_no1) {
                        $el->empl_no1 = $employee->empl_no1;
                        $updateFlag = true;
                    }

                    if ($el->empl_no2 != $employee->empl_no2) {
                        $el->empl_no2 = $employee->empl_no2;
                        $updateFlag = true;
                    }

                    if ($el->rpro_full_name != $employee->rpro_full_name) {
                        $el->rpro_full_name = $employee->rpro_full_name;
                        $updateFlag = true;
                    }

                    if ($updateFlag) {
                        $el->save();
                        $employeesChanged[] = array('before' => $elPrev, 'after' => $el);
                    }

                } else {
                    $el = new EmployeesLookup;
                    $el->active = $employee->active;
                    $el->description = $employee->description;
                    $el->empl_id = $employee->empl_id;
                    $el->empl_name = $employee->empl_name;
                    $el->empl_no1 = $employee->empl_no1;
                    $el->empl_no2 = $employee->empl_no2;
                    $el->rpro_full_name = $employee->rpro_full_name;
                    $el->save();
                    $newEmployees[] = $el;
                }
            }

            if (count(EmployeesLookup::all()) > count($employees)) {
                // We have more employees in our lookup table than Oracle 
                // has. We should delete an employee.
                foreach (EmployeesLookup::all() as $ela) {
                    if (! in_array($ela->empl_id, $activeEmployeesLookup)) {
                        $employeesRemoved[] = $ela;
                        $ela->delete();
                    }
                }
            }

            $empAtts = array('empl_id', 'empl_name', 'rpro_full_name', 'description', 'empl_no1', 'empl_no2');

            echo "\nNew Employees: " . count($newEmployees) . "\n";
            foreach ($newEmployees as $newEmployee) {
                echo "\n";

                $temp = array();

                foreach ($empAtts as $attribute) {
                    $temp[$attribute] = $newEmployee->$attribute;
                }

                echo json_encode($temp) . "\n";
            }

            echo "\nEmployees Changed: " . count($employeesChanged) . "\n";
            foreach ($employeesChanged as $changedEmployee) {
                echo "\n";

                $tempBefore = array();
                foreach ($empAtts as $attribute) {
                    $tempBefore[$attribute] = $changedEmployee['before']->$attribute;
                }
                echo json_encode($tempBefore) . "\n";

                $tempAfter = array();
                foreach ($empAtts as $attribute) {
                    $tempAfter[$attribute] = $changedEmployee['after']->$attribute;
                }
                echo json_encode($tempAfter) . "\n";

            }

            echo "\nEmployees Removed: " . count($employeesRemoved) . "\n";
            foreach ($employeesRemoved as $removedEmployee) {
                echo "\n";

                $temp = array();

                foreach ($empAtts as $attribute) {
                    $temp[$attribute] = $removedEmployee->$attribute;
                }

                echo json_encode($temp) . "\n";
            }

        } catch(Exception $e) {
            echo $e->getMessage();
            exit(1);
        }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			// array('example', InputArgument::REQUIRED, 'An example argument.'),
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
			// array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
