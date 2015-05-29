@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-7">
        <h3>Employee Lookup</h3>

        <p>To look up an employee enter their Employee Number (aka ADP Number) below.</p>
        <br />

        <div class="form-inline">
            <div class="form-group">
                <input type="text" class="form-control" id="emp-num" placeholder="Enter ADP Number">
            </div>
            <button type="button" id="do-check" class="btn btn-primary">Check</button>
        </div>
        <br />
        <span id="searching" class="hidden"><em>Searching </em><img src="/images/ajax-loader-arrows.gif"></span>
        <blockquote id="results" class="hidden"></blockquote>
    </div>
</div>

@stop
