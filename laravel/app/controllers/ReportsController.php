<?php

class ReportsController extends BaseController
{
    public function getIndex()
    {
        return View::make('pages.reports.index');
    }

    public function getBudgetSalesPlan()
    {
        $extraHead = '
            <script src="/js/reports/budgetsalesplan/budgetsalesplan.js" type="text/javascript" charset="utf-8"></script>
            <script src="/js/moment.min.js" type="text/javascript" charset="utf-8"></script>
        ';

        return View::make(
            'pages.reports.budgetsalesplan',
            array (
                'extraHead' => $extraHead
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
