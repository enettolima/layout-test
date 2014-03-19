<?php

class SchedulerController extends BaseController
{

    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
    }

    protected function ensurePermission($stringPermission, $moreInfo = null)
    {
        $hasPermission = true;

        $user = Auth::user();

        if (! $user->hasRole('Store' . Session::get('storeContext'))) {
            $hasPermission = false;
        }

        if (! $user->can($stringPermission)) {
            $hasPermission = false;
        }

        return $hasPermission;

        if (! $hasPermission) {
            Log::info('need to redirect');
            return View::make(
                'pages.permissionDenied', array(
                    'moreInfo' => $moreInfo
                )
            );
        }

    }

    public function getIndex()
    {
        return Redirect::to('/scheduler/week-overview');
    }

    public function getWeekOverview()
    {

        if (! $this->ensurePermission('scheduler_manage')) {

            $moreInfo = "Click here to request access to this...";

            return View::make(
                'pages.permissionDenied', array(
                    'moreInfo' => $moreInfo
                )
            );
        }


        if (! $currentWeekOf = Input::get('weekOf')) {
            $currentWeekOf = '2014-02-23';
        }

        $extraHead = '
            <script src="/js/bonsai-0.4.1.min.js" type="text/javascript" charset="utf-8"></script>
        ';

        return View::make(
            'pages.scheduler.weekOverview', array(
                'extraHead' => $extraHead,
                'currentWeekOf' => $currentWeekOf,
            )
        );
    }

    public function getDayPlanner()
    {

        if (! $this->ensurePermission('scheduler_manage')) {

            $moreInfo = "Click here to request access to this...";

            return View::make(
                'pages.permissionDenied', array(
                    'moreInfo' => $moreInfo
                )
            );
        }

        $extraHead = '
            <link rel="stylesheet" href="/css/scheduler/fullcalendar.css" />
            <link rel="stylesheet" href="/css/schdeduler/fullcalendar.print.css" media="print" />
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
                'selectorDateFormat' => $selectorDateFormat
            )
        );
    }
}
