<?php

class SchedulerController extends BaseController
{
    protected $userHasAccess = false;
    protected $userCanManage = false;

    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
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

    public function getWeekOverview()
    {
        $this->initAccess();

        if (! $this->userHasAccess) {
            Log::info(__METHOD__ . " access denied for user "  . Auth::user()->username);
            return Response::view('pages.permissionDenied');
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
                'prevSchedules' => $prevSchedules
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

        $extraHead = '
            <link rel="stylesheet" href="/css/scheduler/fullcalendar.css" />
            <link rel="stylesheet" href="/css/scheduler/fullcalendar.print.css" media="print" />
        ';

        $weekOf = Request::input('weekOf');
        $dayOffset = Request::input('dayOffset');
        $targetDay = date('Y-m-d', strtotime($weekOf) + ($dayOffset * 86400));
        $selectorDateFormat = 'D, M jS, Y';

        return View::make(
            'pages.scheduler.dayPlanner', array(
                'extraHead' => $extraHead,
                'weekOf' => $weekOf,
                'dayOffset' => $dayOffset,
                'targetDay' => $targetDay,
                'selectorDateFormat' => $selectorDateFormat,
                'userCanManage' => $this->userCanManage
            )
        );
    }
}
