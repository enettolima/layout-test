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
                Store = '301' and
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
            $returnval[$result->BDWeekday][$result->PROF_HOUR_NEW]['budget'] = $result->HR_BUDGET;
            $returnval[$result->BDWeekday][$result->PROF_HOUR_NEW]['percent'] = $result->PROF_PER;
        }

        return Response::json($returnval);
    }

    /*
     * Currently a "stub" function which will probably be hooked into Oracle
     */
    public function getEmployees()
    {
        // To Protect
        $returnval = array();
        $returnval[] = array("userId" => "000FOO" , "firstName" => "Zero"  , "lastName" => "Mossberg");
        $returnval[] = array("userId" => "001FOO" , "firstName" => "One"   , "lastName" => "Harvey");
        $returnval[] = array("userId" => "002FOO" , "firstName" => "Two"   , "lastName" => "Barlow");
        $returnval[] = array("userId" => "003FOO" , "firstName" => "Three" , "lastName" => "Brock");
        $returnval[] = array("userId" => "004FOO" , "firstName" => "Four"  , "lastName" => "Mcmillan");
        $returnval[] = array("userId" => "005BAR" , "firstName" => "Five"  , "lastName" => "Fisher");
        $returnval[] = array("userId" => "006BAR" , "firstName" => "Six"   , "lastName" => "Patrick");
        $returnval[] = array("userId" => "007BAR" , "firstName" => "Seven" , "lastName" => "Oneil");
        $returnval[] = array("userId" => "008BAR" , "firstName" => "Eight" , "lastName" => "Simpson");
        $returnval[] = array("userId" => "009BAR" , "firstName" => "Nine"  , "lastName" => "House");
        $returnval[] = array("userId" => "010BAR" , "firstName" => "Ten"   , "lastName" => "Blevins");
        return Response::json($returnval);
    }

    public function getSetSchedulerCurrentWeekOf($string)
    {

        if (preg_match('/^\d\d\d\d-\d\d-\d\d$/', Request::segment(3))) {

            Session::set('schedulerCurrentWeekOf', Request::segment(3));

            /*
            if (Session::has('schedulerCurrentWeekOf')) {
                echo "I has currentWeekOf and it is " . Session::get('schedulerCurrentWeekOf');
            }
            */
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
