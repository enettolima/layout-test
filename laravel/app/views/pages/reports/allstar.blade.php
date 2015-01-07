@extends('layouts.default')

@section('content')

<style type="text/css" media="all">
    .report-option {
        background:#F8F8F8;
        padding:10px;
    }

    .report-options-bar {
        display:block;
        background:#F8F8F8;
        border:1px solid #E7E7E7;
    }
</style>

<div class="row">
    <div class="col-xs-2 report-option">
        Choose Range:
        <select id="allstar-report-range" class="form-control">
            <option value="month">Month</option>
            <option value="week">Week</option>
            <!-- <option value="day">One Day</option> -->
        </select>
    </div>
    <div id="allstar-options-week" class="allstar-options" style="display:none;">
        <div class="col-xs-4 report-option"> 
            Choose Week:
            <select id="allstar-week" class="form-control"></select>
        </div>
    </div>
    <div id="allstar-options-month" class="allstar-options" style="display:none;">
        <div class="col-xs-2 report-option">
            Choose Month:
            <select id="allstar-month" class="form-control"></select>
        </div>
    </div>
    <div id="allstar-options-run" class="allstar-options" style="display:none;">
        <div class="col-xs-1 report-option">
            <br />
            <button class="btn btn-primary">Run</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-9">
        <h3 id="report-header">Report Header</h3>
    </div>
</div>

<input type='hidden' id='storeNumber' value='{{ Session::get('storeContext') }}'>

<div class="row">
    <div class="col-xs-9">
        <table class="table table-striped" id="report-data">
        </table>
    </div>
</div>

@stop
