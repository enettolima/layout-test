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

        $noisy = false;

        try {

            if (! $noisy){
                $this->info('Retrieving orders from Magento...');
            }

	if ($this->argument('fromDate') && $this->argument('toDate')) {

            $from = trim($this->argument('fromDate'));
            if (! $this->valiDate($from)) {
                throw new Exception("invalid from date '$from'");
            }

            $to = trim($this->argument('toDate'));
            if (! $this->valiDate($to)) {
                throw new Exception("invalid to date '$from'");
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
                $mageURL = 'https://shop.earthboundtrading.com/ebtutil/orders/getsummary.php?from='.$chunkFrom.'&to='.$chunkTo.'&statuses=complete';

                if ($noisy) {
                    $this->info($mageURL);
                }

                $magentoOrderRequest = Requests::get($mageURL, array(), array('timeout'=>60, 'verify'=>false));
                if ($magentoOrderRequest->success) {
                    $chunkOrders = json_decode($magentoOrderRequest->body)->data->orders;

                    if (count($chunkOrders) > 0) {

                        if ($noisy) {
                            $this->info('Got ' . count($chunkOrders) . ' orders');
                        }

                        $allMageOrders = array_merge($allMageOrders, $chunkOrders);
                    } else {
                        if ($noisy) {
                            $this->info('Got ' . count($chunkOrders) . ' orders!!!!');
                        }
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

                    if (isset($rpReceipt->data)) {

                        $matchResults['pass'][] = array('datapoint' => 'exists', 'mage' => 'yes', 'rp' => 'yes');

                        $mageTotal = (float) $mageOrder->total;
                        $rpTotal = (float) $rpReceipt->data->totalreceipt;

                        $mageTax = (float) $mageOrder->tax;
                        $rpTax = (float) $rpReceipt->data->ext_tax;

                        $mageQty = (int) $mageOrder->qtysold;
                        $rpQty = (int) $rpReceipt->data->qtysold;

                        $testInfo = array('datapoint' => 'total', 'mage' => $mageTotal, 'rp' => $rpTotal);
                        if (($mageTotal === $rpTotal) || abs($mageTotal - $rpTotal) <= .01) {
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
                    $this->report($mageOrder->increment_id, $mageOrder->created_at, $matchResults);
                }
            }

        } catch(Exception $e) {
            echo $e->getMessage();
            exit(1);
        }
    }

    protected function report($i, $c, $results) {
        $showPass = false;

        if (count($results['fail']) > 0) {
            $failString = '';

            foreach ($results['fail'] as $fail) {
                $failString .= "[{$fail['datapoint']} mage={$fail['mage']} rp={$fail['rp']}] ";
            }

            $this->info("FAIL $i on $c => $failString");
        } else {
            if ($showPass) {
                $this->info("PASS $i");
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
			array('fromDate', InputArgument::OPTIONAL, 'From Date'),
			array('toDate', InputArgument::OPTIONAL, 'From Date'),
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
