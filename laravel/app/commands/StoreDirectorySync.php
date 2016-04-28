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

            $stores = DB::connection('sqlsrv_ebt')->select('select * from "Store Directory" where "ONLINE" = \'True\' and "E-ACTIVE" = \'True\'');

            $this->info("Got " . count($stores) . " stores from 'Store Directory' on sqlsrv_ebt.");

            $opHours = DB::connection('sqlsrv_ebt')->select('select * from SCHED_WEEKLY_OP_HOURS');

            $this->info("Got " . count($opHours) . " hours from SCHED_WEEKLY_OP_HOURS on sqlsrv_ebt.");

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
                    $directory[$ohStoreNumber]->{'hours'}[$ohWeekDay]['open'] = $ohOpen;
                    $directory[$ohStoreNumber]->{'hours'}[$ohWeekDay]['close'] = $ohClose;
                }
            }

            $directory = $this->toAlgoliaFormat($directory);
            $this->info("Indexing " . count($directory) . " records to Algolia...");
            $this->pushToAlgolia($directory);

		}catch (Exception $e){

            $this->info("BORKED: " . $e->getMessage());
		}
	}

    /**
     * @param $data Array of objects containing location information from database
     *
     * @return Algolia-specific Array of objects
     *
     * Changes for Algolia:
     * - Algolia needs its geo location data in a specific structure.
     * - We want to strip out locations with no lat & lng
     */
    protected function toAlgoliaFormat($data)
    {

        $this->info("Preparing data for Algolia...");

        $returnval = array();

        foreach ($data as $key => $val) {

            if ($data[$key]->{'lat'} == "" || $data[$key]->{'lng'} == "") {
                $this->info("Skipping #" . $val->{'number'} . " - " . $val->{'name'} . " -- No Lat & Lng");
                continue;
            }

            $val->{'_geoloc'}['lat'] = $val->{'lat'};
            $val->{'_geoloc'}['lng'] = $val->{'lng'};

            $returnval[] = $val;
        }

        return $returnval;
    }

    protected function pushToAlgolia($data)
    {

        AlgoliaLocation::clearIndices();

        foreach ($data as $key => $val) {

            $aRecord = new AlgoliaLocation();
            $aRecord->{'objectID'} = $val->{'number'};

            // Import all properties in $val to $aRecord

            foreach (get_object_vars($val) as $k=>$v){
                $aRecord->$k = $v;
            }

            $aRecord->pushToIndex();
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
