<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CronGetMagentoShippingData extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
	protected $name = 'cron:get-magento-shipping-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download shipping information from Magento for processing orders and store in csv on network for UPS Worldship.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function valiDate($date, $date_format='Y-m-d')
    {
        $time = strtotime($date);

        $is_valid = date($date_format, $time) == $date;

        return ($is_valid);
    }

    public function fire()
    {
        try {

            // Try to open file first; might not be able to
            $fp = fopen($_ENV['magento_shipping_csv_directory'] . '/mageship.csv', 'w');

            $this->info('Retrieving orders from Magento...');

            if ($this->option('from') || $this->option('to')) {

                if (! ($this->option('from') && $this->option('to'))) {
                    throw new Exception("Invalid execution: if specifying either 'from' or 'to', both must be specified.");
                }

                $from = trim($this->option('from'));
                if (! $this->valiDate($from)) {
                    throw new Exception("invalid from date '$from'");
                }

                $to = trim($this->option('to'));
                if (! $this->valiDate($to)) {
                    throw new Exception("invalid to date '$from'");
                }

                if (strtotime($to) < strtotime($from)) {
                    throw new Exception("Invalid execution: 'to' date is earlier than 'from' date and you aren't Marty McFly.");
                }

            }else{
                $from = date("Y-m-d", strtotime("15 days ago"));
                $to = date("Y-m-d");
            }

            $daysDiff = (strtotime($to) - strtotime($from)) / 86400;

            if ($daysDiff < 0) {
                throw new Exception("from date $from is older than to date $to");
            } elseif ($daysDiff == 0) {
                $to = date('Y-m-d', strtotime($to) + 86400);
                $daysDiff = 1;
            }

            $allMageOrders = array();

            for ($day=0; $day<=$daysDiff; $day++) {

                $chunkFrom = date('Y-m-d', strtotime($from) + ($day * 86400));
                $chunkTo = date('Y-m-d', strtotime($from) + (($day+1) * 86400));
                $mageURL = 'https://shop.earthboundtrading.com/ebtutil/orders/getsummary.php?from='.$chunkFrom.'&to='.$chunkTo.'&statuses=processing';

                $magentoOrderRequest = Requests::get($mageURL, array(), array('timeout'=>60, 'verify'=>false));
                if ($magentoOrderRequest->success) {
                    $chunkOrders = json_decode($magentoOrderRequest->body)->data->orders;

                    if (count($chunkOrders) > 0) {
                        $allMageOrders = array_merge($allMageOrders, $chunkOrders);
                    } 
                }

                $this->info("$chunkFrom to $chunkTo: Got " . count($chunkOrders) . " orders.");
            }

            if (count($allMageOrders) > 0) {

                $this->info("Done. Got " . count($allMageOrders) . " orders...");

                $orderData = array();

                foreach ($allMageOrders as $mageOrder) {
                    $outO = new stdclass;
                    $outO->Order_Num = $mageOrder->increment_id;
                    $outO->Order_Num_Trunc = preg_replace('/^10+/', '', $mageOrder->increment_id);
                    $outO->Name = $mageOrder->shipaddress_firstname . ' ' . $mageOrder->shipaddress_lastname;
                    $outO->Address1 = $mageOrder->shipaddress_address[0];
                    if (isset($mageOrder->shipaddress_address[1])) {
                        $outO->Address2 = $mageOrder->shipaddress_address[1];
                    } else {
                        $outO->Address2 = '';
                    }
                        
                    $outO->City = $mageOrder->shipaddress_city;
                    $outO->State = $mageOrder->shipaddress_state;
                    $outO->Zip = $mageOrder->shipaddress_zip;
                    $outO->Phone = $mageOrder->shipaddress_phone;
                    $orderData[] = $outO;
                }



                fputcsv($fp, array_keys((array) $orderData[0]));

                foreach ($orderData as $od) {
                    fputcsv($fp, (array) $od);
                }

                fclose($fp);
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
            array('from', null, InputOption::VALUE_OPTIONAL, 'From Date'),
            array('to', null, InputOption::VALUE_OPTIONAL, 'To Date'),
            array('showpass', null, InputOption::VALUE_NONE, 'Show Passing Orders'),
        );
    }

}
