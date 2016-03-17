@extends('layouts.default')

@section('content')

    <div class="row">
        <div class="col-xs-8">
            <h3>Web Order Form (Temporary)</h3>

            <p>IT is currently building a whole new "WebOrder" for the new Retail Pro.</p>
            <p>Until that is complete <strong>you'll need to use this form for your weekly order instead of the old  Weborder</strong>.</p>
            <p>To submit your order simply add the appropriate Retail Pro IDs and Quantities to the following form. </p>
            <blockquote>Note you are adding by the *CASE*!</blockquote>
        </div>
    </div>

    <h4>Specify Your Order Below:</h4>

    <div class="row">
        <div class="col-xs-10">

            <div id="web-order-items"></div>
        </div>
    </div>

    <span class="web-order-saved-at"></span>


    <span id="searching" class="hidden"><em>Searching </em><img src="/images/ajax-loader-arrows.gif"></span>

@stop
