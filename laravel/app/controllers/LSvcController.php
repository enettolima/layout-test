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
