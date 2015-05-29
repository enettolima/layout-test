@extends('layouts.default')

@section('content')

<div class="container">
	@if ($store_info['message'])
		<div id="message-container">
			<div class="alert alert-dismissible {{ $store_info['type'] }}" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			{{ $store_info['message'] }}</div>
		</div>
	@endif
	<div class="page-header well">
		@if ($store_info['found'])
			<h2>{{ $store_info['store_name'] }} / {{ $store_info['code'] }}</h2>
			<p>Address: {{ $store_info['address'] }} </p>
			<p>Phone: {{ $store_info['phone'] }} </p>
		@endif
	</div>

	<div id="leases_table">
		<button class="btn btn-success" data-toggle="modal" data-target="#lease-modal" id="create-new-lease"><i class="fa fa-plus icon-white"></i> Upload a new document</button><br><br>
		<div id="lease-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog" >
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;  </button>
						<h4 class="modal-title" id="myModalLabel">Attach Document</h4>
					</div>

					<div class="modal-body">
						<div id="message-modal"></div>
						<form id="lease-add-form" method="post" id="noteForm" name="lease-add-form" action="/leases/create-document" enctype="multipart/form-data">
							<div class="form-group">
								<label for="label" class="control-label">Label:</label>
								<input type="text" class="form-control" name="label" id="label">
							</div>
							<div class="form-group field-type-uploader" >
									<div class="uploader form-uploader">
										<ol id="uploaded-files-3127" class="uploaded-files"></ol>
									</div>
									<button data-id="file" id="uploader-button-file" type="button" class="btn btn-default btn-sm uploader-button" data-original-title="" title="">
									Upload <i class="fa fa-download"></i>
									</button>
								</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<input type="hidden" id="code" name="code" value="{{ $store_info['code'] }}">
							<input type="submit" class="btn btn-primary" value="Save changes">
						</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		@if ($store_info['docs_found'])
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<td><b>Creation Date</b></td>
						<td><b>Label</b></td>
						<td><b>Filename</b></td>
						<td><b>Download</b></td>
						<td><b>Delete</b></td>
					</tr>
				</thead>
				<tbody>
					@foreach ($docs as $doc)
						@if ($store_info['flash'])
							<tr id="row" class="flash">
							<?php $store_info['flash'] =false; ?>
						@else
							<tr id="row">
						@endif
							<input type="hidden" name="id" id="id" value="{{ $doc->ID_FILE }}">
							<td>{{ $doc->CREATED_DATE }}</td>
							<td>{{ $doc->LABEL }}</td>
							<td>{{ $doc->FILENAME }}</td>
							<td><a href="/leases/download-document/?file={{ $doc->FILENAME }}" ><i class="fa fa-download"></i></a></td>
							<td><a href="#" ><i class="fa fa-trash"></i></a></td>

						</tr>
					@endforeach
				</tbody>
			</table>
		@else
			<span id="spinny"><i class="fa fa-file-o fa-4x"></i><br><h3>No documents found for this location!</h3></span>
		@endif
	</div>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
		<form id="lease-add-form" method="post" id="noteForm" name="lease-add-form" action="/leases/delete-document" >
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Delete Document</h4>
				</div>
				<div class="modal-body">
					<p>Are you sure you want to delete the document <span id="file_name"></span>?</p>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="doc_id" id="doc_id" value="">
					<input type="hidden" name="doc_filename" id="doc_filename" value="">
					<input type="hidden" id="code" name="code" value="{{ $store_info['code'] }}">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<input type="submit" class="btn btn-danger" value="Delete">
				</div>
			</div>

		</form>
  </div>
</div>
</div><!-- End of container -->
@stop
