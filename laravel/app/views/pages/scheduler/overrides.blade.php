@extends('layouts.default')

@section('content')


<div class="row">
	<div class="col-xs-6">
		<h3>Upcoming Overrides:</h3>

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

        <p>To add an override please specify the following parameters:</p>

		<script>
			$(function() {
				$( "#date" ).datepicker();
				$( "#openTime" ).timepicker();
				$( "#closeTime" ).timepicker();
			});
		</script>


        <link rel="stylesheet" href="/css/jquery.timepicker.css" title="" type="text/css" />
        <script src="/js/jquery.timepicker.min.js" type="text/javascript" charset="utf-8"></script>

		<form class="form-horizontal" role="form" action="" method="POST">
            <div class="form-group">
                <label for="datepicker" class="col-xs-2 control-label">Date:</label>
                <div class="col-xs-4">
                    <input class="form-control" type="text" id="date" name="date">
                </div>
            </div>

            <div class="form-group">
                <label for="datepicker" class="col-xs-2 control-label">Open:</label>
                <div class="col-xs-3">
                    <input class="form-control" type="text" id="openTime" name="openTime">
                </div>
            </div>

            <div class="form-group">
                <label for="datepicker" class="col-xs-2 control-label">Close:</label>
                <div class="col-xs-3">
                    <input class="form-control" type="text" id="closeTime" name="closeTime">
                </div>
            </div>

             <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                      <button id="add-override" class="btn btn-primary">Add Override</button>
                </div>
              </div>
		</form>

	</div>

@stop
