<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class StoreDirectorySync extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'cron:store-directory-sync';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync Store Directory Information with external services such as website and Google.';

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

            $directory = array();

            $stores = DB::connection('sqlsrv_ebt')->select('select * from "Store Directory" where "ONLINE" = \'True\'');

            $this->info("Got " . count($stores) . " stores");

            $opHours = DB::connection('sqlsrv_ebt')->select('select * from SCHED_WEEKLY_OP_HOURS');

            $this->info("Got " . count($opHours) . " hours");

            foreach ($stores as $store_key => $store){

                $storeObj = new stdClass();

                $storeObj->{'number'} = (int) trim($store->{'Code #'});
                $storeObj->{'name'} = $store->{'STORE NAME'};
                $storeObj->{'street'} = $store->{'STREET'};
                $storeObj->{'suite'} = $store->{'STE'};
                $storeObj->{'city'} = $store->{'CITY'};
                $storeObj->{'state'} = $store->{'STATE'};
                $storeObj->{'zip'} = $store->{'ZIP'};
                $storeObj->{'phone'} = $store->{'PHONE'};
                $storeObj->{'lat'} = $store->{'Latitude'};
                $storeObj->{'lng'} = $store->{'Longitude'};

                $directory[$storeObj->{'number'}] = $storeObj;

            }

            foreach ($opHours as $hour_key => $hour) {

                $dayKey = array();
                $dayKey["1"] = 'sun';
                $dayKey["2"] = 'mon';
                $dayKey["3"] = 'tue';
                $dayKey["4"] = 'wed';
                $dayKey["5"] = 'thu';
                $dayKey["6"] = 'fri';
                $dayKey["7"] = 'sat';

                $ohStoreNumber = (int)trim($hour->{'CODE'});
                $ohWeekDay = $dayKey[$hour->{'WEEKDAY'}];
                $ohOpen = date("H:i:s", mktime($hour->{'HR_OPEN_MIL'}, 0, 0, 0, 0, 0));
                $ohClose = date("H:i:s", mktime($hour->{'HR_CLOSE_MIL'}, 0, 0, 0, 0, 0));

                if (isset($directory[$ohStoreNumber])) {
                    $directory[$ohStoreNumber]->{'Hours'}[$ohWeekDay]['open'] = $ohOpen;
                    $directory[$ohStoreNumber]->{'Hours'}[$ohWeekDay]['close'] = $ohClose;
                }
            }

            $this->info("Algolia JSON Follows:");
            $this->info($this->toAlgolia($directory));

		}catch (Exception $e){

            $this->info("BORKED: " . $e->getMessage());

		}
	}

    /**
     * Transform our directory data for Algolia in the following ways:
     * - Break out of store # index
     * - Create a _geoloc array containing lat & lng
     * - Strip out stores w/o lat & lng
     * - Return as JSON
     */
    protected function toAlgolia($data)
    {

        $returnval = array();

        foreach ($data as $key => $val) {


            if ($data[$key]->{'lat'} == "" || $data[$key]->{'lng'} == "") {
                continue;
            }

            $val->{'_geoloc'}['lat'] = $val->{'lat'};
            $val->{'_geoloc'}['lng'] = $val->{'lng'};

            $returnval[] = $val;
        }

        return json_encode($returnval);
    }

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{

        return array(
//			array('example', InputArgument::REQUIRED, 'An example argument.'),
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
//			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
