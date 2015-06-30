@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-10">
        <h3>Music Request Management</h3>

		<h4>Open Requests ({{ count($openRequests) }})</h4>

		<table class="table table-striped table-hover music-request-admin">

		@foreach ($openRequests as $request)
			<tr data-request-id="{{ $request->id }}">
				<td>{{ $request->empid }}</td>
				<td>{{ $request->empname }}</td>
				<td>{{ Carbon::parse($request->created_at)->toDayDateTimeString() }}</td>
				<td>{{ substr($request->request, 0, 60) }}</td>
			</tr>
		@endforeach

		</table>

		<h4>Closed Requests ({{ count($closedRequests) }})</h4>

		<table class="table table-striped table-hover music-request-admin">

		@foreach ($closedRequests as $request)
			<tr data-request-id="{{ $request->id }}">
				<td>{{ $request->empid }}</td>
				<td>{{ $request->empname }}</td>
				<td>{{ Carbon::parse($request->created_at)->toDayDateTimeString() }}</td>
				<td>{{ substr($request->request, 0, 60) }}</td>
			</tr>
		@endforeach

		</table>

    </div>
</div>

@stop
