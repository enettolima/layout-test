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
        $storeNumber= Request::segment(3);
        //Range type will always come as range from now on
        $asRangeType= Request::segment(4);
        //Getting date as unix format
        $asDateFrom = Request::segment(5);
        $asDateTo   = Request::segment(6);
        //Parsing in m/d/Y format
        $from       = str_replace("-","/",$asDateFrom);
        $to         = str_replace("-","/",$asDateTo);

        //Converting dates to a format so we can calculate the amount of days
        $start      = strtotime($from);
        $end        = strtotime($to);
        $days_between = ceil(abs($end - $start) / 86400);
        if($days_between > 31){
          return Response::json(array('msg' => 'Invalid date range! Maximum amount of days allowed is 31!'), 400);
        }

        //Checking if end date is higher than start date
        if($start > $end){
          return Response::json(array('msg' => 'Invalid date range! Please try again!'), 400);
        }
        //Building store procedure call
        $detailsSQL = "WEB_GET_ALLSTAR_NEW '$storeNumber','D','$from','$to'";
        $totalsSQL  = "WEB_GET_ALLSTAR_NEW '$storeNumber','T','$from','$to'";

        //Executing store procedure call
        $detailsRes = DB::connection('sqlsrv_ebtgoogle')->select($detailsSQL);
        $totalsRes  = DB::connection('sqlsrv_ebtgoogle')->select($totalsSQL);

        //Log::info('Check date from date is '.$from.' to is '.$to, $array_debug);
        return Response::json(array('details' => $detailsRes, 'totals' => $totalsRes, 'from' => $from, 'to' => $to));
    }

    //Load the report per employee by month
    public function getReportsAllStarByMonth()
    {
        $storeNumber= Request::segment(3);
        //Range type will always come as range from now on
        $asRangeType= Request::segment(4);
        //Getting date as unix format
        $asDateFrom = Request::segment(5);
        $asDateTo   = Request::segment(6);
        $asEmpCode  = Request::segment(7);
        //Parsing in m/d/Y format
        $from       = str_replace("-","/",$asDateFrom);
        $to         = str_replace("-","/",$asDateTo);

        //Converting dates to a format so we can calculate the amount of days
        $start      = strtotime($from);
        $end        = strtotime($to);
        $days_between = ceil(abs($end - $start) / 86400);
        if($days_between > 366){
          return Response::json(array('msg' => 'Invalid date range! Maximum amount of days allowed is 366!'), 400);
        }

        //Checking if end date is higher than start date
        if($start > $end){
          return Response::json(array('msg' => 'Invalid date range! Please try again!'), 400);
        }
        //Building store procedure call
        $detailsSQL = "WEB_GET_ALLSTAR_by_EMP '$storeNumber','D','$from','$to','$asEmpCode'";
        $totalsSQL  = "WEB_GET_ALLSTAR_by_EMP'$storeNumber','T','$from','$to','$asEmpCode'";

        //Executing store procedure call
        $detailsRes = DB::connection('sqlsrv_ebtgoogle')->select($detailsSQL);
        $totalsRes  = DB::connection('sqlsrv_ebtgoogle')->select($totalsSQL);

        return Response::json(array('details' => $detailsRes, 'totals' => $totalsRes, 'from' => $from, 'to' => $to));
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
      $data             = Input::getContent();
      $vars = json_decode($data);

      $selected_path 	= urlencode($vars->folder);
			$keywords 			= urlencode($vars->keyword);
			if($selected_path == '#' || $selected_path=="0"){
				$selected_path='';
			}
			if($keywords == ''){
				$keywords='';
			}

      $api            = new EBTAPI;
      $results        = $api->get('/esdocs/doc-search?path='.$selected_path.'&keywords='.$keywords);
      $result['data'] = $results->hits->hits;
      $res            = [];

      //Log::info('postDocsSearch results: ',$result);
      foreach($results->hits->hits as $key => $hit){
				$res[$key]=$hit->_source;
			}
			$result['data']  = $res;
			$result['total'] = $results->hits->total;


      //Return json to the docs.js to append the results on jstree


      return Response::json($results);





			//Get data sent by the docs.js
			/*$data = Input::getContent();
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
			return Response::json($results);*/
    }

		public function getFolderSearch()
		{
      $data             = Input::all();
      $api = new EBTAPI;
      $results = $api->get('/esdocs/folder-search?id='.$data['id']);
      $result['data']                    = $results->hits->hits;
      $res=[];
      if(count($results->hits->hits)>0){
        foreach($results->hits->hits as $key => $hit){
          $res[$key]['id']=$hit->_source->full_path;
          $res[$key]['text']=$hit->_source->name;
          $res[$key]['children']=$hit->_source->children;
        }
      }

      Log::info('getFolderSearch results', $result);
      //Return json to the docs.js to append the results on jstree
      return Response::json($res);
      /*
      //Get data sent from passport
      //get input

      //$selected_path =  $_GET['id'];
      if($data['id'] == '' || $data['id'] == '#'){
        $selected_path  = $_ENV['ebt_file_storage'];
      }else{
        $selected_path  = $data['id'].'/';
      }

      $params = [
        'index' => 'docsearch',
        'type' => 'folders',
        'body' => [
          'query' => [
            'match' => [
              'parent' => $selected_path
            ]
          ]
        ]
      ];

      Log::info('Params are ',$params);
      $hosts = [$_ENV['ebt_elasticsearch_host']];// IP + Port
      //$hosts = ['dev.elasticsearch.com:9200'];
      // Instantiate a new ClientBuilder
      $client = \Elasticsearch\ClientBuilder::create()
        ->setHosts($hosts)      // Set the hosts
        ->build();

      $results = $client->search($params);
      //return Response::json($response);

      $result['data']                    = $results['hits']['hits'];
      $res=[];
      if(count($results['hits']['hits'])>0){
        foreach($results['hits']['hits'] as $key => $hit){
          $res[$key]['id']=$hit['_source']['full_path'];
          $res[$key]['text']=$hit['_source']['name'];
          $res[$key]['children']=$hit['_source']['children'];
        }
      }

      Log::info('results', $results);
      //Return json to the docs.js to append the results on jstree
      return Response::json($res);
      */
      /*
      //Get data sent by the docs.js
			$selected_path =  $_GET['id'];
      //$data = array('id' => $selected_path);
      $response = Requests::get($_ENV['ebt_api_host'].'/api/v1/esdocs/folder-search?id='.$selected_path);
      var_dump($response->body);*/
			//Get data sent by the docs.js
			/*$selected_path =  $_GET['id'];
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

      Log::info('Query', array('query'=> $query));
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

      Log::info('results', $results);
			//Return json to the docs.js to append the results on jstree
			return Response::json($res);*/
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
      try{
        $sql = "SELECT * FROM scheduled_inout WHERE id = '$inOutId'";
        $sel = DB::connection('mysql')->select($sql);

        //if sql_id>0 it means that we can save that into mysql
        $sqlId = $sel[0]->sql_id;

        if($sqlId>0){
          //removing on sql Server
          $update_sql = $this->deleteSqlScheduler($sqlId);
          /*if($update_sql!="0"){
            //At this point the php could not save the record on sql meaning that we should not update mySql
            return Response::json(array( 'status' => 0));
            exit();
          }*/
        }
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
      } catch (Exception $e) {
        return Response::json(array( 'status' => 0));
      }
    }

    public function postSchedulerInOutMove()
    {
      $userId     = Request::segment(3);
      $inOutId    = Request::segment(4);
      $storeNumber= Request::segment(5);
      $start      = urldecode(Request::segment(6));
      $end        = urldecode(Request::segment(7));

      //Converting to time so we can make the correct format
      $startStamp = strtotime($start);
      $endStamp = strtotime($end);

      //Converting from time to yyyy-mm-dd hh:mm:ss
      $startDate = date("Y-m-d H:i:s",$startStamp);
      $endDate = date("Y-m-d H:i:s",$endStamp);

      //Extracting date from the string
      $date       = date("Y-m-d",$startStamp);

      try{
        Log::info('Inside try');
        //Selecting the record from MySql to get the sql_id
        $sql = "SELECT * FROM scheduled_inout WHERE id = '$inOutId'";
        Log::info('query is '.$sql);
        $sel = DB::connection('mysql')->select($sql);
        Log::info('Query result is ',$sel);
        //if sql_id>0 it means that we can save that into mysql
        $sqlId = $sel[0]->sql_id;
        $sellable = $sel[0]->sellable;

        if($sqlId>0){
          //Making update on sql Server
          $update_sql = $this->updateSqlScheduler($startDate, $endDate, $sellable, $userId, $sqlId);
          Log::info('inside if with sql id '.$sqlId);
          if($update_sql!="0"){
            Log::info('inside $update_sql '.$update_sql);
            //At this point the php could not save the record on sql meaning that we should not update mySql
            return Response::json(array( 'status' => 0));
            exit();
          }
        }
        $SQL = "
            UPDATE scheduled_inout
            SET
                associate_id = '$userId',
                date_in = '$startDate',
                date_out = '$endDate'
            WHERE
                id = $inOutId
        ";

        if (DB::connection('mysql')->update($SQL)) {
          Log::info('inside db connection '.$SQL);
          $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
          $scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);
          return Response::json(array(
              'status' => 1,
              'scheduleHourLookup' => $scheduleHalfHourLookupRES[0],
              'schedule' => $this->getDaySchedule($storeNumber, $date)
          ));
        } else {
          Log::info('inside else');
          return Response::json(array( 'status' => 0));
        }
      } catch (Exception $e) {
        Log::info('inside catch');
        return Response::json(array( 'status' => 0));
      }
    }

    public function putSchedulerInOutResize()
    {
        $inOutId     = Request::segment(3);
        $storeNumber = Request::segment(4);
        $start      = urldecode(Request::segment(5));
        $end        = urldecode(Request::segment(6));

        //Converting to time so we can make the correct format
        $startStamp = strtotime($start);
        $endStamp = strtotime($end);

        //Converting from time to yyyy-mm-dd hh:mm:ss
        $startDate = date("Y-m-d H:i:s",$startStamp);
        $endDate = date("Y-m-d H:i:s",$endStamp);

        //Extracting date from the string
        $date       = date("Y-m-d",$startStamp);

        try{
          //Selecting the record from MySql to get the sql_id
          $sql = "SELECT * FROM scheduled_inout WHERE id = '$inOutId'";
          $sel = DB::connection('mysql')->select($sql);

          //if sql_id>0 it means that we can save that into mysql
          $sqlId = $sel[0]->sql_id;
          $sellable = $sel[0]->sellable;
          $userId = $sel[0]->associate_id;

          if($sqlId>0){
            //Making update on sql Server
            $update_sql = $this->updateSqlScheduler($startDate, $endDate, $sellable, $userId, $sqlId);
            if($update_sql!="0"){
              //At this point the php could not save the record on sql meaning that we should not update mySql
              return Response::json(array( 'status' => 0));
              exit();
            }
          }

          $SQL = "
              UPDATE scheduled_inout
              SET
                  date_out = '$endDate'
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
        } catch (Exception $e) {
          return Response::json(array( 'status' => 0));
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

                //$sql = "SELECT * FROM scheduled_inout WHERE id = '$inOutId'";
                //$sel = DB::connection('mysql')->select($sql);

                //if sql_id>0 it means that we can save that into mysql
                //$sqlId = $sel[0]->sql_id;
                //removing on sql Server
                //$update_sql = $this->deleteSqlScheduler($sqlId);

                //This will only delete records from this day to 7+ days after
                $this->copSqlScheduler($weekOf, $storeNumber, $userId);
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

            //This will only delete records from this day to 7+ days after
            $this->copSqlScheduler($schedDateTo, $storeNumber);

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

            /*
             * Make sure to call a store procedure to make the whole operation
             * instead on insert one by one
             */
            foreach ($ioRES as $io) {
              $startDate = date( "Y-m-d H:i:s", strtotime( '+'.$dayDiff.'days', strtotime($io->{'date_in'})));
              $endDate = date( "Y-m-d H:i:s", strtotime( '+'.$dayDiff.'days', strtotime($io->{'date_out'})));

              $create_sql = $this->createSqlScheduler($startDate, $endDate, 1, $io->{'associate_id'}, $io->{'store_id'});
              if($create_sql['status']=="0"){//Making sure we can save this into sql server 0=ok, 1=error
                $sqlId = $create_sql['sql_id'];
                $result = DB::connection('mysql')->insert(
                  "insert into scheduled_inout (associate_id, store_id, date_in, date_out, sql_id, sellable) values (?, ?, ?, ?, ?, ?)",
                  array(
                    $io->{'associate_id'},
                    $io->{'store_id'},
                    // Before: Probably DST Problem
                    // date("Y-m-d H:i:s", strtotime($io->{'date_in'}) + (86400 * $dayDiff)),
                    // After:
                    date( "Y-m-d H:i:s", strtotime( '+'.$dayDiff.'days', strtotime($io->{'date_in'}))),
                    date( "Y-m-d H:i:s", strtotime( '+'.$dayDiff.'days', strtotime($io->{'date_out'}))),
                    $sqlId,
                    1
                  )
                );
              }
          }
        } else {
            throw new Exception();
        }

    }

    /*
     * Function used when they update the time manually
     */
    public function putSchedulerInOut()
    {
        $storeNumber = Request::segment(3);
        $inOutId     = Request::segment(4);
        $inString    = Request::segment(5);
        $outString   = Request::segment(6);
        $date        = Request::segment(7);

        $in  = date('Y-m-d H:i:s', strtotime(urldecode($inString)));
        $out = date('Y-m-d H:i:s', strtotime(urldecode($outString)));

        try{

          $outDate = date('Y-m-d', strtotime(urldecode($outString)));
          $outTime = date('H:i:s', strtotime(urldecode($outString)));

          if($outTime=="00:00:00"){
            $outTime = "23:59:59";

            $out = $outDate." ".$outTime;
          }
          //Selecting the record from MySql to get the sql_id
          $sql = "SELECT * FROM scheduled_inout WHERE id = '$inOutId'";
          $sel = DB::connection('mysql')->select($sql);

          //if sql_id>0 it means that we can save that into mysql
          $sqlId = $sel[0]->sql_id;
          $sellable = $sel[0]->sellable;
          $userId = $sel[0]->associate_id;

          if($sqlId>0){
            //Making update on sql Server
            $update_sql = $this->updateSqlScheduler($in, $out, $sellable, $userId, $sqlId);
            if($update_sql!="0"){
              //At this point the php could not save the record on sql meaning that we should not update mySql
              return Response::json(array( 'status' => 0));
              exit();
            }
          }

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
            return Response::json(array( 'status' => 0));
          }
        } catch (Exception $e) {
          return Response::json(array( 'status' => 0));
        }

        /*$SQL = "
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
        }*/
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

        $outDate = date('Y-m-d', strtotime(urldecode($outString)));
        $outTime = date('H:i:s', strtotime(urldecode($outString)));

        if($outTime=="00:00:00"){
          $outTime = "23:59:59";

          $out = $outDate." ".$outTime;
        }
        if ($userId != "undefined") {
          try {
            //$sql = "exec dbo.operInOut 'A', NULL, '".$in."', '".$out."',1, '".$userId."', '".$storeNumber."'";
            //$sqlinsert = DB::connection('sqlsrv_ebtgoogle')->select($sql);
            //Saving this record on sql server(check the ond of this file)
            $create_sql = $this->createSqlScheduler($in, $out, 1, $userId, $storeNumber);
            if($create_sql['status']=="0"){//Making sure we can save this into sql server 0=ok, 1=error
              //Only save on MySql if save on Sql server was successfull
              //Sql is validating if the time is overlapping
              $sql_id = $create_sql['sql_id'];
              $SQL = "
                  INSERT INTO scheduled_inout (
                      associate_id,
                      store_id,
                      date_in,
                      date_out,
                      sql_id,
                      sellable
                  ) VALUES (
                      '$userId',
                      $storeNumber,
                      '$in',
                      '$out',
                      '$sql_id',
                      '1'
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
              }
            }else{
              return Response::json(array(
                  'status' => 0,
                  'id' => 0,
                  'scheduleHourLookup' => 0,
                  'schedule' => $this->getDaySchedule($storeNumber, $date)
              ));
            }
          } catch (Exception $e) {
            return Response::json(array(
                'status' => 0,
                'id' => 0,
                'scheduleHourLookup' => 0,
                'schedule' => $this->getDaySchedule($storeNumber, $date)
            ));
          }
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
                s.`date_out`,
                s.`sql_id`,
                s.`sellable`
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

        $metaSQL = "SELECT data FROM schedule_day_meta WHERE store_id = $storeNumber AND date = '$targetDate'";

        $metaRES = DB::connection('mysql')->select($metaSQL);

        if (! isset($metaRES[0])) {
            $metaRES[0] = (object) '';
            $metaRES[0]->{'data'} = null;
        }

        $metaArray = json_decode($metaRES[0]->{'data'}, true);

        Log::info("meta array",$metaArray);
        Log::info("schedule array",$scheduleRES);
        //Checking if the employee still works at the store,
        //if not remove him/her from the array
        foreach($scheduleRES as $k => $v){
          $check = in_array($scheduleRES[$k]->associate_id, $metaArray['sequence']);
          Log::info("Is employee ".$scheduleRES[$k]->associate_id." exist on sequence->".$check);
          if(!$check){
            unset($scheduleRES[$k]);
          }
        }

        //Time to re-organize the array to return in the order we are going to show on the calendar
        $idarray = array();
        $schedules = array();
        foreach($metaArray['sequence'] as $key => $val){
          foreach($scheduleRES as $ak => $av){
            //Check if the emp id is on the meta array and if its not assinged to the final id yet
            //Just checking the ID on the array to avoid repeating the object
            if($scheduleRES[$ak]->associate_id == $val && !in_array($scheduleRES[$ak]->id, $idarray)){
              $schedules[] = $scheduleRES[$ak];
              $idarray[] = $scheduleRES[$ak]->id;
            }
          }
        }
        $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
        $scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);

        return Response::json(array(
            'meta' => $metaArray,
            'schedule' => $schedules,//$scheduleRES
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

        $metaSQL = "SELECT data FROM schedule_day_meta WHERE store_id = $storeNumber AND date = '$sundayDate'";

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
                    s.`date_out`,
                    s.`sql_id`,
                    s.`sellable`
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

        $targetsSQL = "SELECT Store,DailyBudget,BDWeekday,HR_PROFILE,PROF_HOUR_NEW,PROF_PER,HR_BUDGET,Date,HR_OPEN_MIL,HR_CLOSE_MIL FROM SCHED_BUDGET_PER_HOURS_FINAL_TABLE WHERE Store = '$store' and Date >= convert(datetime, '$from', 101) and Date <= convert(datetime, '$to', 101) ORDER BY Store, Date, PROF_HOUR_NEW";

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

        $targetsSQL = "SELECT CODE,DATE,DayWk,EMPL_NAME,EXT_PRICE,LastPollTime FROM SCHED_SALES_BY_EMPLOYEE WHERE CODE = $store AND DATE >= convert(datetime, '$from', 101) AND DATE <= convert(datetime, '$to', 101)";

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
     * Functions to update the sql side with the scheduler
     */
    public function createSqlScheduler($start, $end, $sellable = 1, $employee_id, $store_number){
      Log::info('createSqlScheduler', array("start"=>$start,
      "end"=>$end,
      "Emp ID"=>$employee_id
      ));
      $sql = "exec dbo.operInOut 'A', NULL, '".$start."', '".$end."',".$sellable.", '".$employee_id."', '".$store_number."'";

      //Log::info('createSqlScheduler', array("op"=>$sql));
      $sqlinsert = DB::connection('sqlsrv_ebtgoogle')->select($sql);

      //Log::info('SqlDetele', $sqlinsert);

      $response['status'] = $sqlinsert[0]->STATUS;
      $response['sql_id'] = $sqlinsert[0]->ID;
      $response['error']  = $sqlinsert[0]->ReasonCode;
      return $response;
    }

    public function updateSqlScheduler($start, $end, $sellable, $employee_id, $sql_id){
      Log::info('updateSqlScheduler', array("start"=>$start,
      "end"=>$end,
      "Emp ID"=>$employee_id
      ));
      $sql = "exec dbo.operInOut 'U', ".$sql_id.", '".$start."', '".$end."',".$sellable.", '".$employee_id."'";
      $sqlupdate = DB::connection('sqlsrv_ebtgoogle')->select($sql);
      //Log::info('updateSqlScheduler query '.$sql);
      //Log::info('updateSqlScheduler', $sqlupdate);
      return $sqlupdate[0]->STATUS;//Status 1 = error, look for ReasonCode on the payload
      //Ex. ["[object] (stdClass: {\"STATUS\":\"1\",\"ID\":null,\"ReasonCode\":\"Does not Exist\"})"]
    }

    public function deleteSqlScheduler($sql_id){
      $sql = "exec dbo.operInOut 'D', ".$sql_id."";
      $sqldelete = DB::connection('sqlsrv_ebtgoogle')->select($sql);
      return true;
    }

    /*
     * Sql server will get the start date and calculate 7 days ahead of
     * that to create the rage tha need to be deleted.
     * In the future this function will delete base on the range and will create the
     * new schedule as well
     */
    public function copSqlScheduler($start, $store_number, $employee_id=0){
      Log::info('Received', array("start"=>$start,
      "Store"=>$store_number,
      "Emp ID"=>$employee_id
      ));
      if($employee_id>0){
        $sql = "exec dbo.operInOutCopy '".$store_number."', '".$start."', NULL, '".$employee_id."'";
      }else{
        $sql = "exec dbo.operInOutCopy '".$store_number."', '".$start."'";
      }
      $sqldelete = DB::connection('sqlsrv_ebtgoogle')->select($sql);
      Log::info('SqlDetele', $sqldelete);
      //return $sqldelete[0]->STATUS;
      return true;
    }
    /////////////////// End of Sql Functions

    /*
     * Function to get is schedule block is sellable or not
     */
    public function getSellableStatus(){
      $inOutId     = Request::segment(3);
      $sql = "SELECT * FROM scheduled_inout WHERE id = '$inOutId'";
      $sel = DB::connection('mysql')->select($sql);

      $sellable = $sel[0]->sellable;

      return Response::json(array('sellable' => $sellable));
    }

    /*
     * Function to update the schedule block to sellable or not
     */
    public function putSchedulerSellable(){
      $inOutId     = Request::segment(3);

      try{
        //Selecting the record from MySql to get the sql_id
        $sql = "SELECT * FROM scheduled_inout WHERE id = '$inOutId'";
        $sel = DB::connection('mysql')->select($sql);

        //if sql_id>0 it means that we can save that into mysql
        $sqlId      = $sel[0]->sql_id;
        $userId     = $sel[0]->associate_id;
        $startDate  = $sel[0]->date_in;
        $endDate    = $sel[0]->date_out;
        $storeNumber= $sel[0]->store_id;

        //Extracting date from the string
        $date       = date("Y-m-d",strtotime($startDate));

        if($sel[0]->sellable==0){
          $sellable = 1;
        }else{
          $sellable = 0;
        }

        if($sqlId>0){
          //Making update on sql Server
          $update_sql = $this->updateSqlScheduler($startDate, $endDate, $sellable, $userId, $sqlId);
          if($update_sql!="0"){
            //At this point the php could not save the record on sql meaning that we should not update mySql
            return Response::json(array( 'status' => 0));
            exit();
          }
        }
        $SQL = "
            UPDATE scheduled_inout
            SET
                sellable = '$sellable'
            WHERE
                id = $inOutId
        ";

        if (DB::connection('mysql')->update($SQL)){
          //return Response::json(array('sellable' => $sellable));
          //$scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
          //$scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);

          return Response::json(array(
              'status' => 1,
              'sellable' => $sellable
              //'scheduleHourLookup' => $scheduleHalfHourLookupRES[0],
              //'schedule' => $this->getDaySchedule($storeNumber, $date)
          ));
        }
      } catch (Exception $e) {
        return Response::json(array( 'status' => 0));
      }
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


    protected function fetchWeborderItems($week_of, $store)
    {

        return Weborder::where('week_of', $week_of)->where('store', $store)->get()->toArray();

    }

    public function postWeborderSave()
    {

        $returnval = array();

        $week_of = Input::get('week_of');
        $store = Session::get('storeContext');
        $items = Input::get('items');


        Weborder::where('week_of', $week_of)->where('store', $store)->delete();

        foreach ($items as $item) {

            if (! isset($item['item_id']) || $item['item_id'] == ""){
                continue;
            }

            if (! isset($item['item_qty']) || $item['item_qty'] == ""){
                continue;
            }

            $wo = new Weborder;
            $wo->week_of = $week_of;
            $wo->store = $store;
            $wo->item_id = $item['item_id'];
            $wo->item_qty = $item['item_qty'];

            $wo->save();

        }

        $returnval['data']['items'] = $this->fetchWeborderItems($week_of, $store);
        $returnval['data']['request'] = Input::all();

        return Response::json($returnval);
    }

    public function getWeborderItems()
    {

        $returnval = array();

        $week_of = Input::get('week_of');
        $store = Session::get('storeContext');

        $returnval['data']['items'] = $this->fetchWeborderItems($week_of, $store);
        $returnval['data']['request'] = Input::all();

        return Response::json($returnval);
    }

    public function getWeborderFile()
    {

        $table = Weborder::all();
        $filename = "weborder.csv";
        $handle = fopen($filename, 'w+');

        fputcsv($handle, array('week_of', 'store', 'item_id', 'item_qty', 'updated_at'));

        foreach($table as $row) {
            fputcsv($handle, array($row['week_of'], $row['store'], $row['item_id'], $row['item_qty'], $row['updated_at']));
        }

        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/csv',
        );

        return Response::download($filename, 'weborder.csv', $headers);

    }


}
