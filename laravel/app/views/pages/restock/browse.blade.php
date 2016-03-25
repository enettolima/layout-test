@extends('layouts.default')

@section('content')

@include('pages.restock.restockheader')
<div id="content-restock">
  @if ($error)
    <div id="restock-message" style="" class="bg-danger">{{$error_msg}}</div>
  @endif

@include('pages.restock.restocknav')


    <div id="item-search" class="form-group">
        <input class="form-control input-lg" id="search-string" type="text" placeholder="Search Items..." autofocus>
        <input type="hidden" id="store-id" value={{$store_id}}>
    </div>

    <span style="display:none;" id="spinny">
      <img height="52" width="52" src="/images/spinner.gif">
    </span>
    <div id="restock-message" style="display:none;" class="alert alert-error"></div>

    <div class="row">
        <div class="col-sm-12">
            <table class="table table-bordered table-striped" id="results"></table>
        </div>
    </div>
</div>
@stop
