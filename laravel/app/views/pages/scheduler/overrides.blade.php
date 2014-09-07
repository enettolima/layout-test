@extends('layouts.default')

@section('content')


<div class="row">
	<div class="col-xs-6">
		<h3>Current Overrides:</h3>

		<small>(Most recent first)</small>
		<table class="table table-striped">
			<tr>
				<th>Date</th>
				<th>Open</th>
				<th>Close</th>
			</tr>
			@foreach ($overrides as $override)
				<tr>
					<td>
						{{ date("D, M dS, Y", strtotime($override->Date)) }}
					</td>
					<td>
						{{ date("h:ia", strtotime($override->OpenHour)) }}
					</td>
					<td>
						{{ date("h:ia", strtotime($override->CloseHour)) }}
					</td>
				</tr>
			@endforeach
		</table>
	</div>
	<div class="col-xs-6">
		<h3>Add Override:</h3>

		<script>
			$(function() {
				$( "#datepicker" ).datepicker();
			});
		</script>

		<form class="form" action="" method="POST">

			<input type="text" id="datepicker">
		</form>

	</div>

@stop
