@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-2">
        Select Month: <select id="monthSelector" class="form-control">
        @foreach($monthOptions as $monthOption)
            <option value="{{ $monthOption['opt'] }}" {{ $monthOption['selected'] ? 'selected' : '' }} >{{ $monthOption['label'] }}</option>
        @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-xs-8">
        <h3 id="report-header">Budget Sales Plan | Store {{ Session::get('storeContext') }} | <span id='report-header-month'></span> | DM: <span id='report-header-dm'></span></h3>
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
