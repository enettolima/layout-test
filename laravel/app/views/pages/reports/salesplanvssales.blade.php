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
        <table class="table table-striped" id="report-data">
        </table>
    </div>
    <div class="col-xs-3">
        <table class="table" id="report-secondary"></table>
    </div>
</div>

@stop
