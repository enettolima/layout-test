<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RpmsyncCompareOrders extends Command {
                            /*
                              From Magento:
                                class stdClass#744 (6) {
                                    public $increment_id =>
                                        string(9) "100002591"
                                        public $created_at =>
                                        string(19) "2014-12-30 23:28:43"
                                        public $total =>
                                        double(77.85)
                                        public $tax =>
                                        int(0)
                                        public $before_tax =>
                                        double(77.85)
                                        public $qtysold =>
                                        int(2)
                                    }

                                From RP: 
                                    class stdClass#717 (1) {
                                        public $data =>
                                            class stdClass#722 (13) {
                                                public $sbs_no =>
                                                string(1) "1"
                                                public $store_no =>
                                                string(2) "74"
                                                public $invc_no =>
                                                string(9) "100002591"
                                                public $invc_sid =>
                                                string(10) "1100002591"
                                                public $created_date =>
                                                string(19) "2014-12-30 17:28:43"
                                                public $ext_price =>
                                                string(5) "74.90"
                                                public $ext_tax =>
                                                string(4) "0.00"
                                                public $ext_return =>
                                                string(1) "0"
                                                public $qtysold =>
                                                string(1) "2"
                                                public $invc_type =>
                                                string(1) "0"
                                                public $so_no =>
                                                NULL
                                                public $so_no_web =>
                                                NULL
                                                public $calc_total =>
                                                string(5) "74.90"
                                            }
                                    }
                             */

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rpmsync:compare-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare completed orders in Magento vs Receipts in Retail Pro.';

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
                $from = date("Y-m-d", strtotime("7 days ago"));
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

            for ($day=0; $day<$daysDiff; $day++) {
                $chunkFrom = date('Y-m-d', strtotime($from) + ($day * 86400));
                $chunkTo = date('Y-m-d', strtotime($from) + (($day+1) * 86400));
                $mageURL = 'http://shopmaster01.earthboundtrading.com/ebtutil/orders/getsummary.php?from='.$chunkFrom.'&to='.$chunkTo.'&statuses=complete';

                $magentoOrderRequest = Requests::get($mageURL, array(), array('timeout'=>300, 'verify'=>false));
                if ($magentoOrderRequest->success) {
                    $chunkOrders = json_decode($magentoOrderRequest->body)->data->orders;

                    if (count($chunkOrders) > 0) {
                        $allMageOrders = array_merge($allMageOrders, $chunkOrders);
                    } 
                }

                $this->info("$chunkFrom to $chunkTo: Got " . count($chunkOrders) . " orders.");
            }

            if (count($allMageOrders) > 0) {

                $this->info("Done. Now checking " . count($allMageOrders) . " orders...");

                $api = new EBTAPI;

                foreach ($allMageOrders as $mageOrder) {

                    $matchResults['pass'] = array();
                    $matchResults['fail'] = array();

                    $rpReceipt = $api->get('/rproorders/order/' . $mageOrder->increment_id);

                    $metaArray = array();
                    $metaArray['OWT'] = $mageOrder->order_was_taxed ? "Y" : "N";
                    $metaArray['SWC'] = $mageOrder->shipping_was_charged ? "Y" : "N";
                    $metaArray['SWT'] = $mageOrder->shipping_was_taxed ? "Y" : "N";

                    if (isset($rpReceipt->data)) {

                        $matchResults['pass'][] = array('datapoint' => 'exists', 'mage' => 'yes', 'rp' => 'yes');

                        $mageTotal = (float) $mageOrder->total;
                        $rpTotal = (float) $rpReceipt->data->totalreceipt;

                        $mageTax = (float) $mageOrder->order_tax_amount;
                        $rpTax = (float) $rpReceipt->data->ext_tax;

                        $mageQty = (int) $mageOrder->qtysold;
                        $rpQty = (int) $rpReceipt->data->qtysold;

                        $testInfo = array('datapoint' => 'total', 'mage' => $mageTotal, 'rp' => $rpTotal);

                        if (($mageTotal === $rpTotal) || abs($mageTotal - $rpTotal) <= .02) {
                            $matchResults['pass'][] = $testInfo;
                        } else {
                            $testInfo['mage'] = number_format($testInfo['mage'], 2);
                            $testInfo['rp'] = number_format($testInfo['rp'], 2);
                            $matchResults['fail'][] = $testInfo;
                        }

                    } else {
                        $matchResults['fail'][] = array('datapoint' => 'exists', 'mage' => 'yes', 'rp' => 'missing');
                    }

                    //report($mageOrder->increment_id, $matchMatches, $matchErrors);
                    $dateString = date("Y-m-d H:i:s", strtotime($mageOrder->created_at));
                    $this->report($mageOrder->increment_id, $dateString, $matchResults, $metaArray);
                }
            }

        } catch(Exception $e) {
            echo $e->getMessage();
            exit(1);
        }
    }

    protected function report($i, $c, $results, $metaArray) {
        $metaString = "(OWT:{$metaArray['OWT']} SWC:{$metaArray['SWC']} SWT:{$metaArray['SWT']})";

        if (count($results['fail']) > 0) {
            $failString = '';

            foreach ($results['fail'] as $fail) {
                $failString .= "[{$fail['datapoint']} mage={$fail['mage']} rp={$fail['rp']}]";
            }

            $this->info("FAIL $i on $c $metaString => $failString");
        } else {
            if ($this->option('showpass')) {
                $this->info("PASS $i on $c $metaString");
            }
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
