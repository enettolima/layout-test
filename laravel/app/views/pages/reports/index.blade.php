@extends('layouts.default')

@section('content')

<h3>Reports</h3>

<div class="row">

    <div class="col-xs-6">

        <dl>
            <dt><a href="reports/budget-sales-plan">Budget Sales Plan</a></dt>
            <dd>Use this report to get a daily summary of Sales vs Budget with percentage change ("over/under") for the entire month. The totals at the bottom of the report will provide you with the overall month numbers.</dd>
        </dl>
        <dl>
            <dt><a href="reports/all-star">All Star</a></dt>
            <dd>Employee Performance. Sales, Hours, Budget by each employee with Statistics ADS, UPT.</dd>
        </dl>
        <dl>
            <dt><a href="reports/sales-plan-vs-sales">Sales Plan vs. Sales</a></dt>
            <dd>Sales and Budget summary from the beginning of the year performed by your store. It will also provide a overall picture of the District and Region for easy comparison.</dd>
        </dl>
    </div>

</div>

@stop
