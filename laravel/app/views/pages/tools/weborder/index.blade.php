@extends('layouts.default')

@section('content')

    <div class="row">
        <div class="col-xs-8">
            <h3>(Temporary) Web Order Form</h3>

            @if ($errors->has())
                @include('includes.errors')
            @endif

            <p>IT is currently building a whole new "WebOrder" to work with the new Retail Pro since the old WebOrder is down.</p>
            <p>Until that is complete <strong>you'll need to use this form for your weekly order instead of the old  Weborder</strong>.</p>
            <p>To give your order simply add the appropriate Retail Pro IDs and Quantities to the following form.</p>
            <p><strong>Be sure to click 'Save Current Order'</strong> to save your choices. You can come back to this later to add/edit your choices.</p>
        </div>
    </div>

    <h4>Specify Your Order Below:</h4>

    <div class="row">
        <div class="col-xs-10">

            <div id="web-order-items"></div>
        </div>
    </div>

    <div class="row" style="padding-top:10px;">
        <div class="col-xs-1">
            <button id="add-row" type="button" class="btn btn-default btn-sm">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add Another Row
            </button>
        </div>
    </div>

    <div class="row" style="padding-top:10px;">
        <div class="col-xs-1">
            <button id="weborder-save" type="button" class="btn btn-primary">
                <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> Click Me to Save Your Choices!
            </button>
        </div>
    </div>



    </div>

    <span id="searching" class="hidden"><em>Searching </em><img src="/images/ajax-loader-arrows.gif"></span>

@stop
