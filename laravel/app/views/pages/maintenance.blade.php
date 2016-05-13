@extends('layouts.default')

@section('content')

<script src="/js/Chart.js/Chart.js"></script>

<div class="jumbotron">

    <h3><i class="fa fa-exclamation-triangle"></i>  {{$title}} </h3>
    <p>
        {{$message}}
    </p>
</div>

@stop
