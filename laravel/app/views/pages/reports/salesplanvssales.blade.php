@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-9">
        <h3 id="report-header">Sales Plan vs. Sales <span id="report-store-number"></span></h3>
    </div>
</div>

<input type='hidden' id='storeNumber' value='{{ Session::get('storeContext') }}'>

<div class="row">
    <div class="col-xs-9">
        <h4>Overview</h4>
        <table class="table table-striped report-table" id="summary-report-data"></table>
    </div>
</div>

<div class="row">
    <div class="col-xs-9">
        <h4>RM Sales</h4>
        <table class="table table-striped report-table" id="rm-report-data"></table>
    </div>
</div>

<div class="row">
    <div class="col-xs-9">
        <h4>DM Sales</h4>
        <table class="table table-striped report-table" id="dm-report-data"></table>
    </div>
</div>

@stop
