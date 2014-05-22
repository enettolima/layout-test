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
	protected $name = 'command:name';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

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

            $stores = json_decode(file_get_contents($_ENV['ebt_api_address'] . "/stores", true));

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

            echo "New Stores: " . count($newStores) . "\n";
            foreach ($newStores as $newStore) {
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
            }

            echo "Stores Changed: " . count($storesChanged) . "\n";
            foreach ($storesChanged as $changedStore) {
                echo "\n";
                echo $changedStore['before']->code . " Before:\n";
                echo $changedStore['before']->store_name . "\n";
                echo $changedStore['before']->street . "\n";
                echo $changedStore['before']->ste . "\n";
                echo $changedStore['before']->state . "\n";
                echo $changedStore['before']->city . "\n";
                echo $changedStore['before']->state . "\n";
                echo $changedStore['before']->zip . "\n";
                echo $changedStore['before']->phone . "\n";
                echo $changedStore['before']->tz_offset . "\n";
                echo $changedStore['before']->is_tourist . "\n\n";

                echo $changedStore['after']->code . " After:\n";
                echo $changedStore['after']->store_name . "\n";
                echo $changedStore['after']->street . "\n";
                echo $changedStore['after']->ste . "\n";
                echo $changedStore['after']->state . "\n";
                echo $changedStore['after']->city . "\n";
                echo $changedStore['after']->state . "\n";
                echo $changedStore['after']->zip . "\n";
                echo $changedStore['after']->phone . "\n";
                echo $changedStore['after']->tz_offset . "\n";
                echo $changedStore['after']->is_tourist . "\n\n";
            }

            echo "Stores Removed: " . count($storesRemoved) . "\n";
            foreach ($storesRemoved as $removedStore) {
                echo $removedStore->code . "\n";
                echo $removedStore->store_name . "\n";
                echo $removedStore->street . "\n";
                echo $removedStore->ste . "\n";
                echo $removedStore->state . "\n";
                echo $removedStore->city . "\n";
                echo $removedStore->state . "\n";
                echo $removedStore->zip . "\n";
                echo $removedStore->phone . "\n";
                echo $removedStore->tz_offset . "\n";
                echo $removedStore->is_tourist . "\n\n";
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
			array('example', InputArgument::REQUIRED, 'An example argument.'),
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
			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}