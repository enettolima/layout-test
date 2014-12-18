@extends('layouts.default')

@section('content')

<h3>Budget Sales Plan &mdash; Store {{ Session::get('storeContext') }} &mdash; Dec 2014</h3>

<input type='hidden' id='storeNumber' value='{{ Session::get('storeContext') }}'>
<input type='hidden' id='reportDate' value='{{ date("Y-m"); }}'>

<table class="table" id="budget-sales-plan">
    <tr>
        <td><em>Loading</em> <img src="/images/ajax-loader-arrows.gif"</td>
    </tr>
</table>
@stop
