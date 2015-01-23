<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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

            $inventoryLog = new Logger('log');
            $inventoryLog->pushHandler(new StreamHandler(storage_path() . '/logs/invsync.' . date('YmdHis') . '.log', Logger::INFO));

            if ($this->option('do_live_inventory') && filter_var($this->option('do_live_inventory'), FILTER_VALIDATE_BOOLEAN)) {
                $mageURL = 'http://shop.earthboundtrading.com';
                $this->info('##SYNCING LIVE INVENTORY!');
            } else {
                $this->info('##Syncing TEST inventory.');
                $mageURL = 'http://testshop.earthboundtrading.com';
            }

            /*
             * STEP 1: Get list of products from Magento we plan on syncing inventory for
             */
            $headers = null;
            $options = null;

            $this->info("Getting products from Magento...");
            $request = Requests::get(
                $mageURL . '/ebtutil/inventory/getproducts.php'//,
                //$headers,
                //$options
            );

            if ($request->success) {
                $this->info("Getting products from Magento complete (success).");
                $mageProducts = json_decode($request->body)->data;
            } else {
                $this->info("Getting products from Magento complete (FAIL).");
                throw new Exception('Error getting product list from Magento');
            }

            /*
             * STEP 2: Get list of all products & inventory for our store in Retail Pro from the API
             * 470 = 74
             */
            $api = new EBTAPI;
            // TODO: Write the following into the api with this query:
            // select item_sid, qty from cms.invn_sbs_qty where store_no = 74;
            $this->info('Getting all inventory from RP...');
            $rpProductsReq = $api->get('/rproproducts/store-products/74');

            if (! isset ($rpProductsReq->errors)) {
                $rpProducts = array();

                foreach ($rpProductsReq->data as $rpProduct) {
                    $rpProducts[$rpProduct->item_sid] = (int) $rpProduct->qty;
                }
                $this->info('Getting all inventory from RP complete.');// 
                $summary['rpProducts'] = count($rpProducts);
            }

            $p = array();

            $matchResults = array();

            $summary['mageProducts'] = count((array) $mageProducts);

            foreach ($mageProducts as $mageProductSid => $mageProductMeta) {
                if (array_key_exists($mageProductSid, $rpProducts)) {
                    $matchResults['matches'][] = array('sid' => $mageProductSid, 'qty' => $rpProducts[$mageProductSid], 'sku' => $mageProductMeta->sku, 'name' => $mageProductMeta->name);
                } else {
                    $matchResults['non-matches'][] = array('sid' => $mageProductSid, 'qty' => null, 'sku' => $mageProductMeta->sku, 'name' => $mageProductMeta->name);
                }
            }

            $summary['productMatches'] = count($matchResults['matches']);
            $summary['productNonMatches'] = count($matchResults['non-matches']);

            foreach ($matchResults['matches'] as $match) {
                $inventoryLog->addInfo("MRPMATCH " . $match['sid'] . ' ' . $match['sku'] . ' ' . $match['name'] . ' ' . $match['qty']);
            }

            foreach ($matchResults['matches'] as $match) {
                $inventoryLog->addInfo("MRPMISS " . $match['sid'] . ' ' . $match['sku'] . ' ' . $match['name'] . ' ' . $match['qty']);
            }


            $this->info('Posting new quantities to Magento...');
            $postResultsReq = Requests::post(
                $mageURL . '/ebtutil/inventory/syncinventory.php',
                array(),
                array('data' => json_encode($matchResults['matches'])),
                array('timeout' => 120)
            );

            if ($postResultsReq->success) {
                $this->info('Posting new quantities to Magento complete (success)');
                $results = json_decode($postResultsReq->body);

                if (isset($results->matches->updated)) {
                    foreach ($results->matches->udpated as $rez) {
                        $inventoryLog->addInfo("UPDATED Date:" . $rez->procTime . ' SID:' . $rez->sid . ' SKU:' . $rez->sku . ' MAGEINV:' . $rez->mageInv . ' MAGEPROC:' . $rez->mageProc . ' RPINV:' . $rez->rpInv . ' NEWQTY:' . $rez->newQty . ' MAGEINSTOCK:' . $rez->mageInStock);
                    }
                    $summary['mageUpdated'] = count($results->matches->updated);
                } else {
                    $summary['mageUpdated'] = 0;
                }

                if (isset($results->matches->notchanged)) {
                    foreach ($results->matches->notchanged as $rez) {
                        $inventoryLog->addInfo("NOCHANGE Date:" . $rez->procTime . ' SID:' . $rez->sid . ' SKU:' . $rez->sku . ' MAGEINV:' . $rez->mageInv . ' MAGEPROC:' . $rez->mageProc . ' RPINV:' . $rez->rpInv . ' NEWQTY:' . $rez->newQty . ' MAGEINSTOCK:' . $rez->mageInStock);
                    }
                    $summary['mageNoChange'] = count($results->matches->notchanged);
                } else {
                    $summary['mageNoChange'] = 0;
                }

            } else {
                echo("POST fail");
            }

            $this->info('');
            $this->info('##SUMMARY');
            $this->info('Products in Magento: ' . $summary['mageProducts']);
            $this->info('Products in Retail Pro: ' . $summary['rpProducts']);
            $this->info('Non-Matching (Skipped) Products: ' . $summary['productNonMatches']);
            $this->info('Matching (Processed) Products: ' . $summary['productMatches']);
            $this->info('Magento Products Changed: ' . $summary['mageUpdated']);
            $this->info('Magento Products Not Changed: ' . $summary['mageNoChange']);

        } catch(Exception $e) {
            echo $e->getMessage() . ' at line ' . $e->getLine();
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
            array('do_live_inventory', null, InputOption::VALUE_OPTIONAL, 'Do live inventory', null),
        );
    }

}
