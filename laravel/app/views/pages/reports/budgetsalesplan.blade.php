@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-2">
        Select Month: <select id="monthSelector" class="form-control">
            <option value="2014-10">Oct 2014</option>
            <option value="2014-11">Nov 2014</option>
            <option selected value="2014-12">Dec 2014</option>
            <option value="2015-01">Jan 2015</option>
            <option value="2015-02">Feb 2015</option>
            <option value="2015-03">Mar 2015</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-xs-8">
        <h3>Budget Sales Plan | Store {{ Session::get('storeContext') }} | <span id='report-header-month'></span> | DM: <span id='report-header-dm'></span></h3>
    </div>
</div>

<input type='hidden' id='storeNumber' value='{{ Session::get('storeContext') }}'>
<input type='hidden' id='reportDate' value='{{ date("Y-m"); }}'>

<div class="row">
    <div class="col-xs-8">
        <table class="table table-striped" id="budget-sales-plan">
        </table>
    </div>
</div>

@stop
