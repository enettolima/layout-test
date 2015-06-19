<?php

class LSvcController extends BaseController
{
    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
    }

    public function getIndex()
    {
        // Log::info('asdf', array('username', Auth::check()));
    }

    public function postIndex()
    {
        // Log::info('asdf', array('username', Auth::check()));
    }

    public function postProductInfo()
    {
        $pcn = Request::segment(3);
        $api = new EBTAPI;
        $res = $api->post("/pims/product-info/$pcn", array('field1' => Input::get('field1')));
        return Response::json($res);
    }

    public function getProductInfo()
    {
        $pcn = Request::segment(3);
        $api = new EBTAPI;
        $res = $api->get("/rproproducts/product-info/$pcn?pi=1");

        return Response::json($res);
    }

    public function getToolsEmployeeLookup()
    {
        $empNum = Request::segment(3);
        $api = new EBTAPI;
        $res = $api->get('/rproemployees/lookup-by-emp-num/' . $empNum);
        return Response::json($res);
	}

    public function getReportsSalesPlanVsSales()
    {
        $storeNumber = Request::segment(3);
        $res = DB::connection('sqlsrv_ebtgoogle')->select("WEB_GET_SALES_PLAN_VS_SALES '$storeNumber'");
        return Response::json(array('data' => $res));
    }

    public function getSchedulerOperationalHours()
    {
        $storeNumber = Request::segment(3);

        $weekOf      = Request::segment(4);
        list($year, $month, $day) = explode('-', $weekOf);
        $weekOf = $month . '/' . $day . '/'  . $year;

        $res = DB::connection('sqlsrv_ebtgoogle')->select("WEB_GET_OPER_HOURS '$storeNumber', '$weekOf'");

        return Response::json(array('data' => $res));
    }

    public function getReportsAllStar()
    {
        $storeNumber = Request::segment(3);
        $asRangeType = Request::segment(4);
        $asRangeVal = Request::segment(5);

        switch ($asRangeType) {
            case 'month':

                list($year, $month) = explode("-", $asRangeVal); //clog::log('asdf');
                $detailsSQL = "WEB_GET_ALLSTAR '$storeNumber', 'D', 'M', '$month', '$year'";
                $totalsSQL = "WEB_GET_ALLSTAR '$storeNumber', 'T', 'M', '$month', '$year'";
                break;

            case 'week':
                list($week, $year) = explode("-", $asRangeVal);
                $detailsSQL = "WEB_GET_ALLSTAR '$storeNumber', 'D', 'W', '$week', '$year'";
                $totalsSQL = "WEB_GET_ALLSTAR '$storeNumber', 'T', 'W', '$week', '$year'";
                break;
            case 'day':
                break;
            default:
                exit(1);
        }

        $detailsRes = DB::connection('sqlsrv_ebtgoogle')->select($detailsSQL);
        $totalsRes = DB::connection('sqlsrv_ebtgoogle')->select($totalsSQL);

        return Response::json(array('details' => $detailsRes, 'totals' => $totalsRes));

    }

    public function getReportsBudgetSalesPlan()
    {
        $storeNumber = Request::segment(3);

        $date = Request::segment(4);

        list($year, $month) = explode('-', $date);

        $details = DB::connection('sqlsrv_ebtgoogle')->select("exec WEB_GET_SALES_PLAN '$storeNumber','$month','$year','D';");
        $totals = DB::connection('sqlsrv_ebtgoogle')->select("exec WEB_GET_SALES_PLAN '$storeNumber','$month','$year','T';");

        return Response::json(array('details' => $details, 'totals' => $totals));
    }

    public function getHoursOverride()
    {
        // GET http://domain.com/lsvc/hours-override
        $storeNumber = Request::segment(3);

        $fromDate = strtotime("today");

        $dateFormat = "Y-m-d H:i:s";

        $sql = "
            SELECT
                *
            FROM
                StoreHoursOverrides
            WHERE
                StoreCode = ? and
                Date >= ?
            ORDER BY
                Date
        ";

        $overrides = DB::connection('sqlsrv_ebtgoogle')->select($sql, array($storeNumber, date($dateFormat, $fromDate)));

        return Response::json($overrides);
    }

    public function postHoursOverride()
    {
        // POST http://domain.com/lsvc/hours-override
        $storeNumber = Request::segment(3);
        $inStamp = Request::segment(4);
        $outStamp = Request::segment(5);
        $dateFormat = "Y-m-d H:i:s";

        $sql = " INSERT INTO StoreHoursOverrides ( StoreCode, Date, OpenHour, CloseHour, ModifiedOn, OpenMil, CloseMil) VALUES ( ?, ?, ?, ?, ?, ?, ?) ";

        $date       = date($dateFormat, strtotime(date("Y-m-d", $inStamp)));
        $openHour   = date($dateFormat, $inStamp);
        $closeHour  = date($dateFormat, $outStamp);
        $modifiedOn = date($dateFormat);
        $openMil    = date("H", $inStamp);
        $closeMil   = date("H", $outStamp);

        $returnval = array();
        $returnval['success'] = 0;

        try {
            DB::connection('sqlsrv_ebtgoogle')->insert($sql, array($storeNumber, $date, $openHour, $closeHour, $modifiedOn, $openMil, $closeMil));
            $returnval['success'] = 1;
        } catch (Exception $e) {

            $message = $e->getMessage();

            $error_reason = null;

            if (preg_match('/error:\s(\d+)/', $message, $matches)) {
                switch ($matches[1]) {
                    case 2627:
                        $error_reason = "Duplicate entry for day";
                        break;
                }
            }

            $returnval['error'] = $error_reason;
        }

        return Response::json($returnval);

    }

    public function deleteHoursOverride()
    {
        // DELETE http://domain.com/lsvc/hours-override
        $storeNumber = Request::segment(3);
    }


    public function postDocsSearch()
    {
			//Get data sent by the docs.js
			$data = Input::getContent();
			$vars = json_decode($data);
			$params2 = array();
			$params2['hosts'] = array($_ENV['ebt_elasticsearch_host']);
			//Start the elasticsearch client plugin
			$client = new Elasticsearch\Client($params2);
			$selected_path 	= $vars->folder;
			$keywords 			= $vars->keyword;
			if($selected_path == '#' || $selected_path=="0"){
				$selected_path='';
			}
			if($keywords == ''){
				$keywords='*';
			}

			//build the query for elasticsearch
			$query = '{
				"size": 999,
				"query": {
					"bool": {
						"must": [
							{
								"regexp": {
									"path.virtual": "'.$selected_path.'.*"
								}
							},
							{
								"query_string": {
									"fields" : ["content","file.filename"],
									"default_operator": "or",
									"query": "'.$keywords.'",
									"fuzziness": 2,
									"use_dis_max" : true
								}
							}
						]
					}
				}
			}';

			//Set index and type for elasticsearch -- using dir previously set as alias for the eb_documents index
			$params['index']                 = 'dir';
			$params['type']                  = 'doc';
			$params['body']                  = $query = trim(preg_replace('/\s+/', ' ', $query));
			$results                         = $client->search($params);
			$res=[];
			foreach($results['hits']['hits'] as $key => $hit){
				$res[$key]=$hit['_source']['file'];
			}
			$result['data']                  =$res;
			$result['total']                 =$results['hits']['total'];
			//Return json to the docs.js to append the results on the screen
			return Response::json($results);
    }

		public function getFolderSearch()
		{
			//Get data sent by the docs.js
			$selected_path =  $_GET['id'];
			if($selected_path == '#'){
				$selected_path='';
			}
			//build the query for elasticsearch
			$query = '{
				"_source": [
					"virtual",
					"name"
				],
				"query": {
					"bool": {
						"must": [
							{
								"regexp": {
									"virtual": "'.$selected_path.'/[^/]*"
								}
							}
						]
					}
				}
			}';

			$params2                           = array();
			$params2['hosts']                  = array($_ENV['ebt_elasticsearch_host']);
			$client                            = new Elasticsearch\Client($params2);
			//Set index and type for elasticsearch -- using dir previously set as alias for the eb_documents index
			$params['index']                   = 'dir';
			$params['type']                    = 'folder';
			$params['body']                    = $query = trim(preg_replace('/\s+/', ' ', $query));
			$results                           = $client->search($params);
			$result['data']                    = $results['hits']['hits'];
			$res=[];
			if(count($results['hits']['hits'])>0){
				foreach($results['hits']['hits'] as $key => $hit){
					$res[$key]['id']=$hit['_source']['virtual'];
					$res[$key]['text']=$hit['_source']['name'];
					$res[$key]['children']=true;
				}
			}
			//Return json to the docs.js to append the results on jstree
			return Response::json($res);
		}

    public function postSchedulerEmailQuickview()
    {
        $store = Input::get('currentStore');
        $weekOf = Input::get('weekOf');
        $recipients = Input::get('recipients');

        $subject = "EBT #$store Schedule for $weekOf";

        Mail::send('emails.scheduler.share', Input::all(), function($message) use ($recipients, $subject)
        {
            foreach ($recipients as $recipient) {
                $message->to($recipient)->subject($subject);
            }
        });
    }

    public function getSchedulerEmployeeInfo()
    {

        $storeNumber = Request::segment(3);

        $weekOf = Request::segment(4);

        $SQL = "
            SELECT
                *
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$weekOf'
        ";

        $RES = DB::connection('mysql')->select($SQL);

        $returnval = array();

        if ($RES[0]->data) {

            $data = json_decode($RES[0]->data);

            foreach ($data->sequence as $user) {

                $returnval[$user] = array();

                if ($userObj = User::where('username', $user)->first()){
                    $returnval[$user]['email'] = $userObj->preferred_email;
                    $returnval[$user]['full_name'] = $userObj->full_name;
                }
            }
        }

        return Response::json(array(
            'storeNumber' => $storeNumber,
            'weekOf' => $weekOf,
            'users' => $returnval
        ));
    }

    public function getSchedulerCsa()
    {
        Auth::logout();
    }

    public function postSchedulerQuickviewShare()
    {
        $storeNumber = Request::segment(3);
        $weekOf = Request::segment(4);
        $userId = Auth::user()->id;

        $rt = new ResourceToken;

        $rt->creator_user_id = $userId;
        $rt->active = 1;
        $rt->expires_at = date("Y-m-d H:i:s", strtotime("+6 week"));
        $rt->resource = "scheduler/quickview/$storeNumber/$weekOf";
        $rt->token = str_random(20);

        if ($rt->save()) {
            return Response::json(array('token' => $rt->token));
        }
    }

    // Todo: protect against unauthorized call
    public function deleteSchedulerInOut()
    {

        $inOutId     = Request::segment(3);
        $storeNumber = Request::segment(4);
        $date        = Request::segment(5);

        $deleteSQL = "
            DELETE FROM
                scheduled_inout
            WHERE
                id = $inOutId
        ";

        if (DB::connection('mysql')->delete($deleteSQL)) {

            $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
            $scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);

            return Response::json(array(
                'status' => 1,
                'scheduleHourLookup' => $scheduleHalfHourLookupRES[0],
                'schedule' => $this->getDaySchedule($storeNumber, $date)
            ));

        } else {
            return Response::json(array('status' => 0));
        }

    }

    public function putSchedulerInOutMove()
    {
        $userId = Request::segment(3);
        $inOutId = Request::segment(4);
        $delta = Request::segment(5);
        $date = Request::segment(6);
        $storeNumber = Request::segment(7);

        $SQL = "
            UPDATE scheduled_inout
            SET
                associate_id = '$userId',
                date_in = DATE_ADD(date_in, INTERVAL $delta MINUTE),
                date_out = DATE_ADD(date_out, INTERVAL $delta MINUTE)
            WHERE
                id = $inOutId
        ";

        if (DB::connection('mysql')->update($SQL)) {

            $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
            $scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);

            return Response::json(array(
                'status' => 1,
                'scheduleHourLookup' => $scheduleHalfHourLookupRES[0],
                'schedule' => $this->getDaySchedule($storeNumber, $date)
            ));
        } else {
            return Response::json(array( 'status' => 0));
        }
    }


    public function putSchedulerInOutResize()
    {
        $inOutId     = Request::segment(3);
        $delta       = Request::segment(4);
        $storeNumber = Request::segment(5);
        $date        = Request::segment(6);

        $SQL = "
            UPDATE scheduled_inout
            SET
                date_out = DATE_ADD(date_out, INTERVAL $delta MINUTE)
            WHERE
                id = $inOutId
        ";

        if (DB::connection('mysql')->update($SQL)) {

            $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
            $scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);

            return Response::json(array(
                'status' => 1,
                'scheduleHourLookup' => $scheduleHalfHourLookupRES[0],
                'schedule' => $this->getDaySchedule($storeNumber, $date)
            ));

        } else {
            return Response::json(array('status' => 0));
        }
    }

    public function deleteSchedulerRemoveUser()
    {

        $storeNumber = Request::segment(3);
        $userId      = Request::segment(4);
        $weekOf      = Request::segment(5);

        $SQL = "
            SELECT
                *
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$weekOf'
        ";

        $RES = DB::connection('mysql')->select($SQL);

        $performUpdate = false;

        if (count($RES) === 1){

            $metaArray = json_decode($RES[0]->{'data'}, true);

            $currentSequence = $metaArray['sequence'];
            if (in_array($userId, $currentSequence)) {
                foreach($currentSequence as $key=>$val) {
                    if ($val == $userId) {
                        unset($currentSequence[$key]);
                        $performUpdate = true;
                    }
                }
            }
        }

        if ($performUpdate) {
            $metaArray['sequence'] = array_values($currentSequence);
            $newData = json_encode($metaArray);
            $updateSQL = "UPDATE schedule_day_meta set data = '$newData' where id = {$RES[0]->{'id'}}";
            $updateRES = DB::connection('mysql')->update($updateSQL);

            if ($updateRES) {

                $nextSunday = date("Y-m-d", strtotime('next sunday', strtotime($weekOf)));

                $deleteScheduleSQL = "
                    DELETE FROM
                        scheduled_inout
                    WHERE
                        date_in >= '$weekOf' AND
                        date_in < '$nextSunday' AND
                        associate_id = '$userId' AND
                        store_id = $storeNumber
                ";

                $deleteScheduleRES = DB::connection('mysql')->delete($deleteScheduleSQL);

                return Response::json(array('status' => 1));
            } else {
                return Response::json(array('status' => 0));
            }

        } else {
            return Response::json(array('status' => 1));
        }
    }

    public function putSchedulerInOutColumn()
    {
        $storeNumber = Request::segment(3);
        $date        = Request::segment(4);
        $userId      = Request::segment(5);

        $SQL = "
            SELECT
                *
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$date'
        ";

        $RES = DB::connection('mysql')->select($SQL);

        $addData = array('type'=>false);

        if (count($RES) === 1){
            $metaArray = json_decode($RES[0]->{'data'}, true);
            $currentSequence = $metaArray['sequence'];
            if (! in_array($userId, $currentSequence)) {
                $currentSequence[] = $userId;
                $metaArray['sequence'] = $currentSequence;
                $newData = json_encode($metaArray);
                $addData['type'] = 'update';
                $addData['SQL'] = "UPDATE schedule_day_meta set data = '$newData' where id = {$RES[0]->{'id'}}";
            }
        } else {
            $metaArray = array();
            $metaArray['sequence'][] = $userId;
            $newData = json_encode($metaArray);
            $addData['type'] = 'insert';
            $addData['SQL'] = "INSERT INTO schedule_day_meta (store_id, date, data) VALUES ($storeNumber, '$date', '$newData')";
        }

        if ($addData['type']) {

            if ($addData['type'] == 'insert') {
                $RES = DB::connection('mysql')->insert($addData['SQL']);
            } elseif ($addData['type'] == 'update') {
                $RES = DB::connection('mysql')->update($addData['SQL']);
            }

            if ($RES) {
                return Response::json(array('status' => 1));
            } else {
                return Response::json(array('status' => 0));
            }
        } else {
            return Response::json(array('status' => 1));
        }
    }

    public function postSchedulerCopySchedule()
    {

        $storeNumber   = Request::segment(3);
        $schedDateFrom = Request::segment(4);
        $schedDateTo   = Request::segment(5);

        // Step 1: Get the metadata row on the FROM
        $metaRes = DB::connection('mysql')->select("select * from schedule_day_meta where store_id = $storeNumber and date = '$schedDateFrom'");

        if (count($metaRes) === 1) {

            // Clear it just in case there's any metadata hanging around
            // Todo: this is probably pretty reckless. I'm not protecting any input.

            // Is the destination schedule really empty? We are having problems with copies blowing away
            // existing schedules
            $copyToRes = DB::connection('mysql')->select("select * from schedule_day_meta where store_id = $storeNumber and date = '$schedDateTo'");

            if (count($copyToRes) !== 0) {
                $data = json_decode($copyToRes[0]->data);
                if (count($data->sequence) !== 0) {
                    throw new Exception("Destination schedule $schedDateTo for $storeNumber not really empty");
                }
            }

            DB::connection('mysql')->delete("delete from schedule_day_meta where store_id = $storeNumber and date = '$schedDateTo'");

            DB::connection('mysql')->insert('insert into schedule_day_meta (store_id, date, data) values (?, ?, ?)', array($storeNumber, $schedDateTo, $metaRes[0]->{'data'}));

            // Step 2: Get all the in/outs

            // Before: Probably DST Problem
            // $fromWeekBoundary = date('Y-m-d', strtotime($schedDateFrom) + (86400 * 7));
            // After:
            $fromWeekBoundary = date('Y-m-d', strtotime('+7days', strtotime($schedDateFrom)));

            $ioSQL = "
                SELECT
                    *
                FROM
                    scheduled_inout
                WHERE
                    store_id = $storeNumber AND
                    date_in >= '$schedDateFrom' AND
                    date_out < '$fromWeekBoundary'
            ";

            $ioRES = DB::connection('mysql')->select($ioSQL);

            // Step 3: clear existing in/outs just in case

            // Before: Probably DST Problem
            // $toWeekBoundary = date('Y-m-d', strtotime($schedDateTo) + (86400 * 7));
            // After:
            $toWeekBoundary = date('Y-m-d', strtotime('+7days', strtotime($schedDateTo)));

            $ioClearSQL = "
                DELETE FROM
                    scheduled_inout
                WHERE
                    store_id = $storeNumber AND
                    date_in >= '$schedDateTo' AND
                    date_out < '$toWeekBoundary'
            ";

            DB::connection('mysql')->delete($ioClearSQL);

            // Before: Probably DST Problem
            //$dayDiff = (strtotime($schedDateTo) - strtotime($schedDateFrom)) / 86400;

            // After:
            $dtSchedDateTo = new DateTime($schedDateTo);
            $dtSchedDateFrom = new DateTime($schedDateFrom);
            $dayDiff = $dtSchedDateTo->diff($dtSchedDateFrom)->format("%a");

            foreach ($ioRES as $io) {

                $result = DB::connection('mysql')->insert(
                    "insert into scheduled_inout (associate_id, store_id, date_in, date_out) values (?, ?, ?, ?)",
                    array(
                        $io->{'associate_id'},
                        $io->{'store_id'},

                        // Before: Probably DST Problem
                        // date("Y-m-d H:i:s", strtotime($io->{'date_in'}) + (86400 * $dayDiff)),

                        // After:
                        date( "Y-m-d H:i:s", strtotime( '+'.$dayDiff.'days', strtotime($io->{'date_in'}))),
                        date( "Y-m-d H:i:s", strtotime( '+'.$dayDiff.'days', strtotime($io->{'date_out'}))),
                    )
                );

            }

        } else {
            throw new Exception();
        }

    }

    public function putSchedulerInOut()
    {
        $storeNumber = Request::segment(3);
        $inOutId     = Request::segment(4);
        $inString    = Request::segment(5);
        $outString   = Request::segment(6);
        $date        = Request::segment(7);

        $in  = date('Y-m-d H:i:s', strtotime(urldecode($inString)));
        $out = date('Y-m-d H:i:s', strtotime(urldecode($outString)));

        $SQL = "
            UPDATE scheduled_inout
            SET
                date_in = '$in',
                date_out = '$out'
            WHERE
                id = $inOutId
        ";

        if (DB::connection('mysql')->update($SQL)) {

            $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
            $scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);

            return Response::json(array(
                'status' => 1,
                'scheduleHourLookup' => $scheduleHalfHourLookupRES[0],
                'schedule' => $this->getDaySchedule($storeNumber, $date)
            ));

        } else {
            return Response::json(array('status' => 0));
        }
    }

    public function postSchedulerInOut()
    {
        $storeNumber = Request::segment(3);
        $userId      = Request::segment(4);
        $inString    = Request::segment(5);
        $outString   = Request::segment(6);
        $date        = Request::segment(7);

        $in  = date('Y-m-d H:i:s', strtotime(urldecode($inString)));
        $out = date('Y-m-d H:i:s', strtotime(urldecode($outString)));

        if ($userId != "undefined") {

            $SQL = "
                INSERT INTO scheduled_inout (
                    associate_id,
                    store_id,
                    date_in,
                    date_out
                ) VALUES (
                    '$userId',
                    $storeNumber,
                    '$in',
                    '$out'
                )
            ";

            if (DB::connection('mysql')->insert($SQL)) {

                $id = DB::connection('mysql')->getPdo()->lastInsertId();

                $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
                $scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);

                return Response::json(array(
                    'status' => 1,
                    'id' => $id,
                    'scheduleHourLookup' => $scheduleHalfHourLookupRES[0],
                    'schedule' => $this->getDaySchedule($storeNumber, $date)
                ));

            } else {
                return Response::json(array('id' => null));
            }
        } else {
            return Response::json(array('id' => null));
        }
    }

    protected function getDaySchedule($storeNumber, $targetDate)
    {
        $date = date('Y-m-d', strtotime($targetDate));

        $returnval = array();

        $scheduleSQL = "
            SELECT
                s.`id`,
                s.`associate_id`,
                s.`store_id`,
                s.`date_in`,
                s.`date_out`
            FROM
                scheduled_inout s
            WHERE
                s.`store_id` = $storeNumber AND
                DATE(date_in) = '$date';
        ";

        $scheduleRES = DB::connection('mysql')->select($scheduleSQL);

        return $scheduleRES;
    }

    public function getSchedulerStoreDaySchedule()
    {
        $storeNumber = Request::segment(3);

        $targetDate = Request::segment(4);

        $scheduleRES = $this->getDaySchedule($storeNumber, $targetDate);

        $date = date('Y-m-d', strtotime($targetDate));

        // Metadata currently resides at the week point...

        $ts = strtotime($targetDate);
        $sundayTimestamp = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
        $targetDate = date('Y-m-d', $sundayTimestamp);

        $metaSQL = "
            SELECT
                data
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$targetDate'
        ";

        $metaRES = DB::connection('mysql')->select($metaSQL);

        if (! isset($metaRES[0])) {
            $metaRES[0] = (object) '';
            $metaRES[0]->{'data'} = null;
        }

        $metaArray = json_decode($metaRES[0]->{'data'}, true);

        $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
        $scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);

        return Response::json(array(
            'meta' => $metaArray,
            'schedule' => $scheduleRES,
            'scheduleHourLookup' => $scheduleHalfHourLookupRES[0]
        ));
    }

    public function getSchedulerStoreWeekSchedule()
    {
        $storeNumber = Request::segment(3);
        $sundayDate = Request::segment(4);

        // Normalize supplied 'sundayDate' to YYYY-MM-DD
        $sundayDate = date('Y-m-d', strtotime($sundayDate));

        $returnval = array();

        $metaSQL = "
            SELECT
                data
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$sundayDate'
        ";

        $metaRES = DB::connection('mysql')->select($metaSQL);

        if (! isset($metaRES[0])) {
            $metaRES[0] = (object) '';
            $metaRES[0]->{'data'} = null;
        }

        $metaArray = json_decode($metaRES[0]->{'data'}, true);

        $metaArray['days'] = array();

        // So we get 7 total days starting from sunday...
        for ($i=0; $i <=6; $i++) {

            // Before: Probably DST Problem
            // $onDate = date('Y-m-d', strtotime($sundayDate) + ($i * 86400));
            // After:
            $onDate = date('Y-m-d', strtotime('+'.$i.'days', strtotime($sundayDate)));

            $onDayName = date("D", strtotime($onDate));
            $onDayMD = date("n/j", strtotime($onDate));

            $metaArray['days'][] = array('Ymd' => $onDate, 'md' => $onDayMD, 'dayName' => $onDayName);

            /*
             * Following is a copy from /storeDaySchedule below, which
             * is not the proper way to do this, but Slim's scope is messing with
             * me. TODO: Refactor this to avoid obvious DRY breakage
             */

            // $querySchedule = "
            $scheduleSQL = "
                SELECT
                    s.`id`,
                    s.`associate_id`,
                    s.`store_id`,
                    s.`date_in`,
                    s.`date_out`
                FROM
                    scheduled_inout s
                WHERE
                    s.`store_id` = $storeNumber AND
                    DATE(date_in) = '$onDate'
            ";

            $scheduleRES = DB::connection('mysql')->select($scheduleSQL);

            $returnval[$onDate] = array('schedule' => $scheduleRES);
        }

        $nr = array();

        $summary = array();

        $e = new EBTScheduler;

        // This: is getting super messy
        foreach ($returnval as $day => $val) {

            // Log::info('val', $val);

            if (! isset($dayNum)) {
                $dayNum = 0;
            }
            $dayArray = array();
            $empArray = array();

            $summary['hoursByDate'][$day] = 0;

            $summary['hoursByDayNum'][$dayNum] = 0;


            foreach ($val['schedule'] as $inoutVal) {

                $empArray[$inoutVal->{'associate_id'}][] = array (
                    'in' => date("H:i", strtotime($inoutVal->{'date_in'})),
                    'out' => date("H:i", strtotime($inoutVal->{'date_out'}))
                );

                $e->setInOut($dayNum, $inoutVal->{'associate_id'}, $inoutVal->{'date_in'}, $inoutVal->{'date_out'});

                $totFoo = strtotime($inoutVal->{'date_out'}) - strtotime($inoutVal->{'date_in'});

                $summary['hoursByDate'][$day] = $summary['hoursByDate'][$day] + ($totFoo / 3600);

                $summary['hoursByDayNum'][$dayNum] = $summary['hoursByDayNum'][$dayNum] + ($totFoo / 3600);

            }

            // $summary['empHoursByDayNum'][$dayNum] = $empSchedArray;

            foreach ($empArray as $empKey=>$empVal) {
                $dayArray[] = array('eid' => $empKey, 'inouts' => $empVal);
            }

            // Ill-advised sorting method START
            // TODO: This probably has the potential to break a lot of things
            // The point of this is to re-sort the employees in the schedule based on their
            // sequence in the meta array
            if (isset($metaArray) && array_key_exists('sequence', $metaArray)) {
                $sorted = array();
                foreach ($metaArray['sequence'] as $sortKey) {
                    foreach ($dayArray as $empOuts) {
                        if ($sortKey == $empOuts['eid']) {
                            $sorted[] = $empOuts;
                        }
                    }
                }
                $dayArray = $sorted;
            }
            // Ill-advised sorting method STOP

            $nr[] = $dayArray;
            $dayNum++;
        }

        $summary['empHoursByDayNum'] = $e->getInOutStringsArray();

        $summary['empHoursByEmp'] = $e->getEmpHoursWeekSummaryArray();

        $returnArray = array('meta' => $metaArray, 'schedule' => $nr, 'summary' => $summary);

        return Response::json($returnArray);
    }

    // TODO: Move this to API
    public function getSchedulerTargets()
    {
        // /scheduler-targets/301/2014-01-01

        $store = Request::segment(3);

        $weekOf = Request::segment(4);

        $from = date("m/d/Y", strtotime($weekOf)); // Sunday, or "Week Of"

        // Before: Probably DST Problem
        // $to = date("m/d/Y", strtotime($weekOf) + (86400 * 6)); // Sunday, or "Week Of"
        // After:
        $to = date("m/d/Y", strtotime('+6days', strtotime($weekOf)));

        $targetsSQL = "
            SELECT
                Store,
                DailyBudget,
                BDWeekday,
                HR_PROFILE,
                PROF_HOUR_NEW,
                PROF_PER,
                HR_BUDGET,
                Date,
                HR_OPEN_MIL,
                HR_CLOSE_MIL
              FROM
                SCHED_BUDGET_PER_HOURS_FINAL_TABLE
              WHERE
                Store = '$store' and
                Date >= convert(datetime, '$from', 101) and
                Date <= convert(datetime, '$to', 101)
              ORDER BY
                Store,
                Date,
                PROF_HOUR_NEW
        ";

        $targetsRES = DB::connection('sqlsrv_ebtgoogle')->select($targetsSQL);

        $returnval = array();

        foreach ($targetsRES as $result) {
            $returnval[$result->BDWeekday]['target'] = $result->DailyBudget;
            $returnval[$result->BDWeekday]['profile'] = $result->HR_PROFILE;
            $returnval[$result->BDWeekday]['open'] = $result->HR_OPEN_MIL;
            $returnval[$result->BDWeekday]['close'] = $result->HR_CLOSE_MIL;
            $returnval[$result->BDWeekday]['hours'][$result->PROF_HOUR_NEW]['budget'] = $result->HR_BUDGET;
            $returnval[$result->BDWeekday]['hours'][$result->PROF_HOUR_NEW]['percent'] = $result->PROF_PER;
        }

        return Response::json($returnval);
    }

    // TODO: Move this to API
    public function getSchedulerActuals()
    {
        // /scheduler-targets/301/2014-01-01

        $store = Request::segment(3);

        $weekOf = Request::segment(4);

        $from = date("m/d/Y", strtotime($weekOf)); // Sunday, or "Week Of"

        // Before: Probably DST Problem
        // $to = date("m/d/Y", strtotime($weekOf) + (86400 * 6));
        // After:
        $to = date("m/d/Y", strtotime('+6days', strtotime($weekOf)));

        $targetsSQL = "
            SELECT
                CODE,
                DATE,
                DayWk,
                EMPL_NAME,
                EXT_PRICE,
                LastPollTime
            FROM
                SCHED_SALES_BY_EMPLOYEE
            WHERE
                CODE = $store AND
                DATE >= convert(datetime, '$from', 101) AND
                DATE <= convert(datetime, '$to', 101)
        ";

        $targetsRES = DB::connection('sqlsrv_ebtgoogle')->select($targetsSQL);

        $returnval = array(
            'summaries' => array(
                'byDate' => array(),
                'byEmp' => array()
            ),
            'total' => 0.00,
            'minPollTimestamp' => null
        );

        foreach ($targetsRES as $result) {

            $dateFmt = date("Y-m-d", strtotime($result->DATE));

            // Fill out the 'byDate' summary
            if (! array_key_exists($dateFmt, $returnval['summaries']['byDate'])) {
                $returnval['summaries']['byDate'][$dateFmt] = array('total' => 0.00, 'emps' => array());
            }

            if (! array_key_exists($result->EMPL_NAME, $returnval['summaries']['byDate'][$dateFmt]['emps'])) {
                $returnval['summaries']['byDate'][$dateFmt]['emps'][$result->EMPL_NAME] = 0.00;
            }

            $returnval['summaries']['byDate'][$dateFmt]['total'] += $result->EXT_PRICE;

            $returnval['summaries']['byDate'][$dateFmt]['emps'][$result->EMPL_NAME] += $result->EXT_PRICE;

            // Fill out the byEmp summary
            if (! array_key_exists($result->EMPL_NAME, $returnval['summaries']['byEmp'])){
                $returnval['summaries']['byEmp'][$result->EMPL_NAME] = array('total' => 0.00, 'dates' => array());
            }

            if (! array_key_exists($dateFmt, $returnval['summaries']['byEmp'][$result->EMPL_NAME]['dates'])) {
                $returnval['summaries']['byEmp'][$result->EMPL_NAME]['dates'][$dateFmt] = 0.00;
            }

            $returnval['summaries']['byEmp'][$result->EMPL_NAME]['dates'][$dateFmt] += $result->EXT_PRICE;
            $returnval['summaries']['byEmp'][$result->EMPL_NAME]['total'] += $result->EXT_PRICE;

            $returnval['total'] += $result->EXT_PRICE;

            if (! $returnval['minPollTimestamp'] || strtotime($result->LastPollTime) < $returnval['minPollTimestamp']) {
                $returnval['minPollTimestamp'] = strtotime($result->LastPollTime);
            }

        }

        return Response::json($returnval);
    }

    /*
     * Currently a "stub" function which will probably be hooked into Oracle
     */
    public function getEmployees()
    {
        $emps = DB::connection('mysql')->table('employees_lookup')->select('empl_name as userId', 'rpro_full_name as fullName', 'empl_no2 as manager')->orderBy('empl_name')->get();
        return Response::json($emps);
    }

    public function getSchedulerSetCurrentWeekOf($string)
    {

        if (preg_match('/^\d\d\d\d-\d\d-\d\d$/', Request::segment(3))) {
            Session::set('schedulerCurrentWeekOf', Request::segment(3));

        }
    }

    public function postCheckStoreAuth()
    {
        $storeNumber = Input::get('storeNumber');

        if (! preg_match('/^\d\d\d$/', $storeNumber)) {
            App::abort(403, "'$storeNumber' not a properly-formatted storeNumber.");
        }

        if (! $username = Input::get('username')) {
            if (! Auth::check()) {
                App::abort(403, "No username passed and no currently logged in user.");
            } else {
                $username = Auth::user()->username;
            }
        }

        if (! preg_match('/^\d\d\d[A-Z]+$/i', $username)) {
            App::abort(403, "'$username' not a properly-formatted username");
        }

        $returnval = array();

        if (! Entrust::hasRole('Store' . $storeNumber)) {
            $returnval['status'] = false;
        } else {
            $returnval['status'] = true;
            Session::set('storeContext', $storeNumber);
        }

        return Response::json($returnval);
    }
}
