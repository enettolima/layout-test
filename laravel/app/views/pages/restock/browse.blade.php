@extends('layouts.default')

@section('content')

<h3>Restock</h3>

<div id="content-restock">

@include('pages.restock.restocknav')


    <div id="item-search" class="form-group">
        <input class="form-control input-lg" type="text" placeholder="Search Items..." autofocus>
    </div>

    <div id="restock-message" style="display:none;"></div>

    <div class="row">
        <div class="col-sm-12">
            <table class="table table-bordered table-striped" id="results"></table>
        </div>
    </div>
</div>
@stop