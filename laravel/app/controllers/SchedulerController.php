<?php

class SchedulerController extends BaseController
{
    protected $userHasAccess = FALSE;
    protected $userCanManage = FALSE;
    protected $isTokenAccess = FALSE;

    /* Require Auth on Everything Here */
    public function __construct()
    {
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

    public function getIndex()
    {
        return Redirect::to('/scheduler/week-overview');
    }

    public function getQuickview()
    {
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
            $prevSchedules = DB::connection('mysql')->select("
                SELECT 
                    * 
                FROM 
                    schedule_day_meta 
                WHERE 
                    store_id = $currentStore
                ORDER BY
                    date DESC
                LIMIT
                    15
            "); 
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

    public function getDayPlanner()
    {
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
