<?php

class ReportsController extends BaseController
{
    public function getIndex()
    {
        return View::make('pages.reports.index');
    }

    public function getBudgetSalesPlan()
    {

        $details = DB::connection('sqlsrv_ebtgoogle')->select("exec WEB_GET_SALES_PLAN '301','11','2014','D';");

        $totals = DB::connection('sqlsrv_ebtgoogle')->select("exec WEB_GET_SALES_PLAN '301','11','2014','T';");

        $extraHead = '
            <script src="/js/reports/budgetsalesplan/budgetsalesplan.js" type="text/javascript" charset="utf-8"></script>
            <script src="/js/moment.min.js" type="text/javascript" charset="utf-8"></script>
        ';

        return View::make(
            'pages.reports.budgetsalesplan',
            array (
                'details' => $details,
                'totals' => $totals,
                'extraHead' => $extraHead
            )
        );

    }
}
