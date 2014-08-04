<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CronWeatherGetAlerts extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cron:weather-get-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get current weather alerts from Wunderground and add to weather alerts db table.';

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

        $sleepTime = 6;

        foreach (StoresLookup::all() as $store) {
            $this->info("On {$store->code} {$store->city}, {$store->state}");
            $cityEnc = rawurlencode($store->city);
            $state = rawurlencode($store->state);

            $contents = file_get_contents("http://api.wunderground.com/api/27603a980e3def63/alerts/q/$state/$cityEnc.json");

            $array = json_decode($contents, true);

            $wa = new WeatherAlert;
            $wa->store_code = $store->code;
            $wa->city = $store->city;
            $wa->state = $store->state;

            if (array_key_exists('alerts', $array) && count($array['alerts']) > 0) {
                $this->info("Alerts found!");

                foreach ($array['alerts'] as $alert) {

                    //$message = $alert['message'];

                    $wa->type = $alert['type'];
                    $wa->description = $alert['description'];
                    $this->info("{$alert['type']} - {$alert['description']}");
                    $wa->alert_date = date("Y-m-d H:i:s", $alert['date_epoch']);
                    $wa->alert_expires = date("Y-m-d H:i:s", $alert['expires_epoch']);
                    $wa->timezone = $alert['tz_short'];

                }
            } else {
                $this->info("All clear.");
                $wa->all_clear = true;
            }


            if ($wa->save()) {
                $this->info("Saved row");
            } else {
                $this->error("Couldn't save row!");
            }

            sleep($sleepTime);
        }

        /*
        if (($handle = fopen("/home/chad/weather/Stores.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $city = $data[2];
                $cityEnc = rawurlencode($city);
                $state = rawurlencode($data[1]);

                $contents = file_get_contents("http://api.wunderground.com/api/27603a980e3def63/alerts/q/$state/$cityEnc.json");

                $array = json_decode($contents, true);

                $storeNumber = $data[0];

                if (array_key_exists('alerts', $array) && count($array['alerts']) > 0) {

                    foreach ($array['alerts'] as $alert) {
                        $alertDate = date("YmdHis", $alert['date_epoch']);
                        $expireDate = date("YmdHis", $alert['expires_epoch']);
                        $type = $alert['type'];
                        $desc = $alert['description'];
                        $tz = $alert['tz_short'];
                        $message = $alert['message'];


                        print "$storeNumber, $city, $state, $alertDate, $expireDate, $type, $desc, $tz\n";
                    }
                } else {
                    $nowarnDate = date("YmdHis");
                    print "$storeNumber, $city, $state, no warnings at $nowarnDate\n";
                }

                $onRow++;

                sleep($sleepTime);
            }

            fclose($handle);
        }
        */
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
