<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CronRefreshStores extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'cron:refresh-stores';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Refresh stores lookup database from Retail Pro.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        try {

            $api = new EBTAPI;
            $stores = $api->get('/stores');

            $onlineStoresLookup = array();
            $storesChanged = array();
            $newStores = array();
            $storesRemoved = array();

            foreach ($stores as $key=>$store) {

                $onlineStoresLookup[] = $store->code;

                if ($sl = StoresLookup::where('code', $store->code)->first()) {

                    $slPrev = clone $sl; // Stash a copy of the original for the report

                    $updateFlag = false;

                    if ($sl->store_name != $store->store_name) {
                        $sl->store_name = $store->store_name;
                        $updateFlag = true;
                    }

                    if ($sl->street != $store->street) {
                        $sl->street = $store->street;
                        $updateFlag = true;
                    }

                    if ($sl->ste != $store->ste) {
                        $sl->ste = $store->ste;
                        $updateFlag = true;
                    }

                    if ($sl->state != $store->state) {
                        $sl->state = $store->state;
                        $updateFlag = true;
                    }

                    if ($sl->city != $store->city) {
                        $sl->city = $store->city;
                        $updateFlag = true;
                    }

                    if ($sl->zip != $store->zip) {
                        $sl->zip = $store->zip;
                        $updateFlag = true;
                    }

                    if ($sl->phone != $store->phone) {
                        $sl->phone = $store->phone;
                        $updateFlag = true;
                    }

                    if ($sl->tz_offset != $store->timezone) {
                        $sl->tz_offset = $store->timezone;
                        $updateFlag = true;
                    }

                    if ($sl->is_tourist != $store->tourist_loca) {
                        $sl->is_tourist = $store->tourist_loca;
                        $updateFlag = true;
                    }

                    if ($updateFlag) {
                        $sl->save();
                        $storesChanged[] = array('before' => $slPrev, 'after' => $sl);
                    }

                } else {
                    $sl = new StoresLookup;
                    $sl->code = $store->code;
                    $sl->store_name = $store->store_name;
                    $sl->street = $store->street;
                    $sl->ste = $store->ste;
                    $sl->state = $store->state;
                    $sl->city = $store->city;
                    $sl->zip = $store->zip;
                    $sl->phone = $store->phone;
                    $sl->tz_offset = $store->timezone;
                    $sl->is_tourist = $store->tourist_loca;
                    $sl->save();
                    $newStores[] = $sl;
                }
            }

            if (count(StoresLookup::all()) > count($stores)) {
                // We have more stores in our lookup table than Oracle 
                // has. Should we delete a store?
                foreach (StoresLookup::all() as $sla) {
                    if (! in_array($sla->code, $onlineStoresLookup)) {
                        $storesRemoved[] = $sla;
                        $sla->delete();
                    }
                }
            }

            $atts = array('code', 'store_name', 'street', 'ste', 'state', 'city', 'zip', 'phone', 'tz_offset', 'is_tourist');

            echo "\nNew Stores: " . count($newStores) . "\n";
            foreach ($newStores as $newStore) {
                $temp = array();
                foreach ($atts as $att) {
                    $temp[$att] = $newStore->$att;
                }
                echo json_encode($temp) . "\n";
                /*
                echo $newStore->code . "\n";
                echo $newStore->store_name . "\n";
                echo $newStore->street . "\n";
                echo $newStore->ste . "\n";
                echo $newStore->state . "\n";
                echo $newStore->city . "\n";
                echo $newStore->state . "\n";
                echo $newStore->zip . "\n";
                echo $newStore->phone . "\n";
                echo $newStore->tz_offset . "\n";
                echo $newStore->is_tourist . "\n\n";
                */
            }

            echo "\nStores Changed: " . count($storesChanged) . "\n";
            foreach ($storesChanged as $changedStore) {

                $temp = array();
                foreach ($atts as $att) {
                    $temp[$att] = $changedStore['before']->$att;
                }
                echo "Before: " . json_encode($temp) . "\n";

                $temp = array();
                foreach ($atts as $att) {
                    $temp[$att] = $changedStore['after']->$att;
                }
                echo "After: " . json_encode($temp) . "\n";
            }

            echo "\nStores Removed: " . count($storesRemoved) . "\n";
            foreach ($storesRemoved as $removedStore) {

                $temp = array();
                foreach ($atts as $att) {
                    $temp[$att] = $removedStore->$att;
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
