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

<div class="row form-inline report-option">
    <div class="form-group">
      <label for="exampleInputName2">Choose Range -- </label>
    </div>
    <div class="form-group">
      <label for="date-from">From: </label>
      <input type="text" class="form-control" id="date-from">
    </div>
    <div class="form-group">
      <label for="date-to">To:</label>
      <input type="text" class="form-control" id="date-to">
    </div>
    <button class="btn btn-primary" id="allstar-run">Run</button>
</div>

<div class="row">
    <div class="col-xs-9">
        <h3 id="report-header">Report Header</h3>
    </div>
</div>

<input type='hidden' id='storeNumber' value='{{ Session::get('storeContext') }}'>

<div class="row">
    <div class="col-xs-12">
        <table class="table table-striped" id="report-data">
        </table>
    </div>
</div>
<div class="row">
	<div class="col-xs-6">
			<table class="table" id="report-secondary"></table>
	</div>
</div>

@stop
