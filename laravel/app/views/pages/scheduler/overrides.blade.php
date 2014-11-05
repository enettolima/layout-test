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
                <th></th>
			</tr>
			@foreach ($overrides as $override)
				<tr>
					<td class="override-date">
						{{ date("D, M dS, Y", strtotime($override->Date)) }}
					</td>
					<td class="override-open">
						{{ date("h:ia", strtotime($override->OpenHour)) }}
					</td>
					<td class="override-close">
						{{ date("h:ia", strtotime($override->CloseHour)) }}
					</td>
                    <td class="override-controls">
                        <a 
                            href="" 
                            data-toggle="modal" 
                            data-target="#remove-override-modal" 
                            data-override-id="{{ $override->ID }}" 
                            class="small remove-override">
                                <span class="glyphicon glyphicon-remove"></span>
                        </a>
                    </td>
				</tr>
			@endforeach
		</table>
	</div>

    <!-- Delete Override Modal -->
    <div id="remove-override-modal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Confirm Override Deletion</h4>
                </div>
                <div id="remove-override-modal-content" class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <a id="remove-override-modal-confirm" type="button" class="btn btn-danger">Yes - Delete It</a> 
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Override Modal -->

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
