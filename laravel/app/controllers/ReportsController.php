<?php

class ReportsController extends BaseController
{
    public function getIndex()
    {
        return View::make('pages.reports.index');
    }

    public function getBudgetSalesPlan()
    {

        $monthsPast = 6;
        $monthsFuture = 3;
        $monthOptions = array();

        $startMonth = date("Y-m-01", strtotime("-$monthsPast months"));

        for ($month=0; $month < $monthsPast + $monthsFuture; $month++) {

            $thisMonth = strtotime($startMonth . "+$month months");

            $selected = false;

            if (date("Y-m") == date("Y-m", $thisMonth)) {
                $selected = true;
            }

            $monthOptions[] = array(
                'full'     => date("Y-m-d", $thisMonth),
                'opt'      => date("Y-m", $thisMonth),
                'label'    => date("M Y", $thisMonth),
                'selected' => $selected
            );
        }

        $extraHead = '
            <script src="/js/reports/budgetsalesplan/budgetsalesplan.js" type="text/javascript" charset="utf-8"></script>
            <script src="/js/moment.min.js" type="text/javascript" charset="utf-8"></script>
        ';

        return View::make(
            'pages.reports.budgetsalesplan',
            array (
                'extraHead' => $extraHead,
                'monthOptions' => $monthOptions
            )
        );
    }

    public function getAllStar()
    {
        $extraHead = '
            <script src="/js/reports/allstar/allstar.js" type="text/javascript" charset="utf-8"></script>
            <script src="/js/moment.min.js" type="text/javascript" charset="utf-8"></script>
            <script src="/js/jquery.tablesorter.min.js" type="text/javascript" charset="utf-8"></script>
        ';

        return View::make(
            'pages.reports.allstar',
            array (
                'extraHead' => $extraHead
            )
        );
    }
}
