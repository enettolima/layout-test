<?php

class SchedulerController extends BaseController
{
    protected $userHasAccess = FALSE;
    protected $userCanManage = FALSE;
    protected $isTokenAccess = FALSE;
    protected $isMaintenanceMode = FALSE;

    /* Require Auth on Everything Here */
    public function __construct()
    {
        try {
          $this->isMaintenanceMode = $_ENV['scheduler_maintenance'];
        } catch (Exception $e) {
          $this->isMaintenanceMode = FALSE;
        }

        if ((Auth::check() === FALSE) && ($token = Request::get('token'))) {

            // Get that token...
            $token = ResourceToken::where('resource', Request::path())
                ->where('token', $token)
                ->where('expires_at', '>', date("Y-m-d H:i:s"))
                ->where('active', 1)
                ->get();

            if ($token->count() === 1) {
                $validToken = $token->first();

                if ($user = User::find($validToken->creator_user_id)) {
                    Session::set('storeContext', Request::segment(3));
                    Auth::login($user);
                    $this->isTokenAccess = TRUE;
                    UserLog::logToken($user->username, $validToken);
                }
            }
        }

        $this->beforeFilter('auth', array());

    }

    public function __destruct()
    {
        if ($this->isTokenAccess) {
            // This won't work because it will happen before our lsvc calls
            // which need authentication. Maybe I should even be making those calls
            // with tokens, similar to the way I'm calling the actual API?
            // Regardless TODO: Security Risk -> currently I'm using javascript to call the
            // api to log out after the page is written on token access
            // Auth::logout();
        }
    }

    /*
     * Todo: refactor this so that 1) it makes sense and 2) we get better
     * "no access" feedback
     */
    protected function initAccess()
    {
        $user = Auth::user();
        if ($user->hasRole('Store' . Session::get('storeContext'))) {
            if ($user->hasRole('District Manager') || $user->hasRole('Manager') || $user->hasRole('Assistant Manager'))
            {
                $this->userHasAccess = true;
                $this->userCanManage = true;
            } elseif ($user->hasRole('Associate')) {
                $this->userHasAccess = true;
                $this->userCanManage = false;
            }
        }
    }

    public function getOverrideHours()
    {
        if($this->isMaintenanceMode){
          return View::make('pages.scheduler.maintenance');
        }
        if (! $store = Session::get('storeContext')) {
            die("couldn't get store context");
        }

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

        $overrides = DB::connection('sqlsrv_ebtgoogle')->select($sql, array($store, date($dateFormat, $fromDate)));

        $extraHead  = '<script src="/js/jquery-ui-1.10.3.custom.js" type="text/javascript" charset="utf-8"></script>';
        $extraHead .= '<script src="/js/scheduler/overrides.js" type="text/javascript" charset="utf-8"></script>';

        $data = array(
            'overrides' => $overrides,
            'extraHead' => $extraHead
        );

        return View::make( 'pages.scheduler.overrides', $data );
    }

    public function postOverrideHours()
    {
        if($this->isMaintenanceMode){
          return View::make('pages.scheduler.maintenance');
        }

        $storeNumber = Session::get('storeContext');
        $inStamp =  strtotime(Input::get('date') . ' ' . Input::get('openTime'));
        $outStamp = strtotime(Input::get('date') . ' ' . Input::get('closeTime'));
        $dateFormat = "Y-m-d H:i:s";

        if (! ($outStamp > $inStamp)) {
            return Redirect::to('scheduler/override-hours')->with('message', 'Override not added - Close time earlier than Open!');
        }

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
                        $error_reason = "Duplicate entry for day.";
                        break;
                }
            }

            $returnval['error'] = $error_reason;
        }

        if ($returnval['success']) {
            return Redirect::to('scheduler/override-hours')->with('message', 'Override has been added!');
        } else {
            return Redirect::to('scheduler/override-hours')->with('message', 'Error adding override. ' . $error_reason);
        }
    }

    public function getOverrideHoursDelete()
    {
        if($this->isMaintenanceMode){
          return View::make('pages.scheduler.maintenance');
        }

        $overrideId = Request::segment(3);

        $userHasAccess = false;
        $overrideExists = false;

        try {
            // Step 1: Get Store # for this Override
            $sql = "SELECT * FROM StoreHoursOverrides where id = ?";
            $res = DB::connection('sqlsrv_ebtgoogle')->select($sql, array($overrideId));

            if (count($res) === 1) {
                $overrideExists = true;
                $user = Auth::user();

                if ($user->hasRole('Store' . trim($res[0]->StoreCode))) {
                    if ($user->hasRole('District Manager') || $user->hasRole('Manager') || $user->hasRole('Assistant Manager'))
                    {
                        $userHasAccess = true;
                    }
                }
            }
        } catch (Exception $e) {
            return Redirect::to('scheduler/override-hours')->with('message', 'Override not deleted due to database error (1)');
        }

        if ($userHasAccess) {
            if ($overrideExists) {
                try {
                    $sql = "DELETE FROM StoreHoursOverrides where ID = ?";
                    $res = DB::connection('sqlsrv_ebtgoogle')->delete($sql, array($overrideId));
                } catch (Exception $e) {
                    return Redirect::to('scheduler/override-hours')->with('message', 'Override not deleted due to database error (1)');
                }

                return Redirect::to('scheduler/override-hours')->with('message', 'Override deleted.');
            } else {
                return Redirect::to('scheduler/override-hours')->with('message', 'Override not deleted - not found.');
            }
        } else {
            return Redirect::to('scheduler/override-hours')->with('message', 'Override not deleted - access denied.');
        }

    }


    public function getIndex()
    {
      if($this->isMaintenanceMode){
        return View::make('pages.scheduler.maintenance');
      }else{
        return Redirect::to('/scheduler/week-overview');
      }
      //$this->checkMaintenance();


    }

    public function getQuickview()
    {
        if($this->isMaintenanceMode){
          return View::make('pages.scheduler.maintenance');
        }
        $storeNumber = Request::segment(3);
        $weekOf      = Request::segment(4);

        // TODO: add in checks to make sure people can't put in their own urls

        $this->initAccess();

        if (! $this->userHasAccess) {
            Log::info(__METHOD__ . " access denied for user "  . Auth::user()->username);
            return Response::view('pages.permissionDenied');
        }

        $extraHead = '';

        $dateHeaderFormat = "D, M d, Y";
        $sunDate = strtotime($weekOf);
        $sunFormatted = date($dateHeaderFormat, $sunDate);
        $satDate = strtotime('+6days', strtotime($weekOf));
        $satFormatted = date($dateHeaderFormat, $satDate);

        $scheduleHeader = "EBT $storeNumber Schedule &mdash; $sunFormatted - $satFormatted";

        $data = array(
            'isTokenAccess'  => $this->isTokenAccess,
            'scheduleHeader' => $scheduleHeader,
            'storeNumber'    => $storeNumber,
            'weekOf'         => $weekOf,
            'extraHead'      => $extraHead,
        );

        return View::make( 'pages.scheduler.quickview', $data );
    }

    public function getWeekOverview()
    {
        if($this->isMaintenanceMode){
          return View::make('pages.scheduler.maintenance');
        }
        $this->initAccess();

        if (! $this->userHasAccess) {
            Log::info(__METHOD__ . " access denied for user "  . Auth::user()->username);
            return Response::view('pages.permissionDenied');
        }

        if (! Session::has('schedulerCurrentWeekOf')) {
            $schedulerCurrentWeekOf = date('Y-m-d', strtotime('last sunday'));
            Session::set('schedulerCurrentWeekOf', $schedulerCurrentWeekOf);
        } else {
            $schedulerCurrentWeekOf = Session::get('schedulerCurrentWeekOf');
        }

        $extraHead = '<script src="/js/bonsai-0.4.1.min.js" type="text/javascript" charset="utf-8"></script>';
        $prevSchedules = array();

        // TODO: Find a more appropriate way of going about this.
        // This data should probably be the product of an API call
        if ($currentStore = Session::get('storeContext')) {
          $query = "SELECT * FROM schedule_day_meta WHERE store_id = $currentStore ORDER BY date DESC LIMIT 15";
          $prevSchedules = DB::connection('mysql')->select($query);
        }

        return View::make(
            'pages.scheduler.weekOverview', array(
                'extraHead' => $extraHead,
                'userCanManage' => $this->userCanManage,
                'prevSchedules' => $prevSchedules,
                'schedulerCurrentWeekOf' => $schedulerCurrentWeekOf
            )
        );
    }

    public function getNewDayPlanner()
    {
        if($this->isMaintenanceMode){
          return View::make('pages.scheduler.maintenance');
        }
        $this->initAccess();

        if (! $this->userHasAccess) {
            Log::info(__METHOD__ . " access denied for user "  . Auth::user()->username);
            return Response::view('pages.permissionDenied');
        }

        $extraHead = '
            <link rel="stylesheet" href="/css/scheduler/fullcalendar.css" />
            <link rel="stylesheet" href="/css/scheduler/fullcalendar.print.css" media="print" />
            <script src="/js/moment.min.js" type="text/javascript" charset="utf-8"></script>
        ';

        $weekOf = Request::input('weekOf');
        $dayOffset = Request::input('dayOffset');
        $targetDay = date('Y-m-d', strtotime('+' . $dayOffset. 'days', strtotime($weekOf)));
        $selectorDateFormat = 'D, M jS, Y';

        return View::make(
            'pages.scheduler.newDayPlanner', array(
                'extraHead' => $extraHead,
                'weekOf' => $weekOf,
                'dayOffset' => $dayOffset,
                'targetDay' => $targetDay,
                'selectorDateFormat' => $selectorDateFormat,
                'userCanManage' => $this->userCanManage
            )
        );
    }

    public function getDayPlanner()
    {
        if($this->isMaintenanceMode){
          return View::make('pages.scheduler.maintenance');
        }
        $this->initAccess();

        if (! $this->userHasAccess) {
            Log::info(__METHOD__ . " access denied for user "  . Auth::user()->username);
            return Response::view('pages.permissionDenied');
        }

        if (! Session::has('schedulerCurrentWeekOf')) {
            $schedulerCurrentWeekOf = date('Y-m-d', strtotime('last sunday'));
            Session::set('schedulerCurrentWeekOf', $schedulerCurrentWeekOf);
        } else {
            $schedulerCurrentWeekOf = Session::get('schedulerCurrentWeekOf');
        }

        $extraHead = '
            <link rel="stylesheet" href="/css/scheduler/fullcalendar.css" />
            <link rel="stylesheet" href="/css/scheduler/fullcalendar.print.css" media="print" />
        ';

        $weekOf = Request::input('weekOf');
        $dayOffset = Request::input('dayOffset');
        $targetDay = date('Y-m-d', strtotime('+' . $dayOffset. 'days', strtotime($weekOf)));
        $selectorDateFormat = 'D, M jS, Y';

        return View::make(
            'pages.scheduler.dayPlanner', array(
                'extraHead' => $extraHead,
                'weekOf' => $weekOf,
                'dayOffset' => $dayOffset,
                'targetDay' => $targetDay,
                'selectorDateFormat' => $selectorDateFormat,
                'userCanManage' => $this->userCanManage,
                'schedulerCurrentWeekOf' => $schedulerCurrentWeekOf
            )
        );
    }
}
