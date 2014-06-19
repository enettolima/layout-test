<?php

class LSvcController extends BaseController
{
    public function getIndex()
    {
        // Log::info('asdf', array('username', Auth::check()));
    }

    public function postIndex()
    {
        // Log::info('asdf', array('username', Auth::check()));
    }

    public function deleteSchedulerInOut()
    {
        $inOutId = Request::segment(3);
        $storeNumber = Request::segment(4);
        $date = Request::segment(5);

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

        // So we get 7 total days starting from sunday...
        for ($i=0; $i <=6; $i++) {

            $onDate = date('Y-m-d', strtotime($sundayDate) + ($i * 86400));

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

    public function getSchedulerTargets()
    {
        // /scheduler-targets/301/2014-01-01

        $store = Request::segment(3);

        $weekOf = Request::segment(4);

        $from = date("m/d/Y", strtotime($weekOf)); // Sunday, or "Week Of"

        $to = date("m/d/Y", strtotime($weekOf) + (86400 * 6)); // Sunday, or "Week Of"

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

        $targetsRES = DB::connection('sqlsrv')->select($targetsSQL);

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

    /*
     * Currently a "stub" function which will probably be hooked into Oracle
     */
    public function getEmployees()
    {

        $emps = DB::connection('mysql')->table('employees_lookup')->select('empl_name as userId', 'rpro_full_name as fullName')->orderBy('empl_name')->get();

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

        if (! preg_match('/^[A-Z]+$/i', $username)) {
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
