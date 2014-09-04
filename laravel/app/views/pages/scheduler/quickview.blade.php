@extends('layouts.barebones')

@section('content')

<input class="hidden" id="week-of" value="{{ $weekOf }}">
<input class="hidden" id="store-number" value="{{ $storeNumber }}">

<div class="quickview">

    <div class="row">
        <div class="col-xs-8">
            <h4>{{ $scheduleHeader }}</h4>
        </div>
        <div class="col-xs-4"></div>
    </div>

    <table id="quickview" class="table">
        <tr>
            <td><em>Loading</em> <img src="/images/ajax-loader-arrows.gif"</td>
        </tr>
    </table>

</div>

<script src="/js/jquery-git.js"></script>
<script src="/js/jquery-ui-1.10.3.custom.js"></script>
<script src="/js/scheduler/summary-functions.js" type="text/javascript" charset="utf-8"></script>
<script src="/js/scheduler/employees.js" type="text/javascript" charset="utf-8"></script>
<script src="/js/scheduler/quickview.js" type="text/javascript" charset="utf-8"></script>

@stop
