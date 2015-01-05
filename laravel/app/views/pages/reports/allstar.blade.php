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
           <!--  <option value="week">Week</option> -->
            <option value="month">Month</option>
            <!-- <option value="day">One Day</option> -->
        </select>
    </div>
    <div id="allstar-options-week" class="allstar-options" style="display:none;">
        <div class="col-xs-3 report-option"> 
            Choose Week:
            <select id="allstar-week-number" class="form-control">
                <!-- <option value="40&#45;2014">40 &#45; Sun, Dec 20th, 2014</option> -->
                <!-- <option value="41&#45;2014">41 &#45; Sun, Dec 20th, 2014</option> -->
                <!-- <option value="42&#45;2014">42 &#45; Sun, Dec 20th, 2014</option> -->
                <!-- <option value="43&#45;2014">43 &#45; Sun, Dec 20th, 2014</option> -->
                <!-- <option value="44&#45;2014">44 &#45; Sun, Dec 20th, 2014</option> -->
            </select>
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
    <div class="col-xs-8">
        <h3 id="report-header">Report Header</h3>
    </div>
</div>

<input type='hidden' id='storeNumber' value='{{ Session::get('storeContext') }}'>

<div class="row">
    <div class="col-xs-8">
        <table class="table table-striped" id="report-data">
        </table>
    </div>
</div>

@stop
