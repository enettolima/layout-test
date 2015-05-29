@extends('layouts.default')

@section('content')

<div class="container">

	<h1 id="lease_title">Lease Management</h1>
	<br>

	<div id="stores_table">
		<table class="table table-striped table-hover">
				<thead>
					<tr>
						<td>Store Code</td>
						<td>Store Name</td>
						<td>City</td>
						<td>State</td>
						<td>Edit</td>
					</tr>
				</thead>
				<tbody>
					@foreach($stores as $key => $value)
						@if ($value->e_active == 0)
							<tr id="row_{{ $value->code }}" class="disabled">
						@else
							<tr id="row_{{ $value->code }}">
						@endif
								<td>{{ $value->code }}</td>
								<td>{{ $value->store_name }}</td>
								<td>{{ $value->city }}</td>
								<td>{{ $value->state }}</td>
								<td><a href="leases/store-information/?code={{ $value->code }}" ><i class="fa fa-list fa-lg"></i></a></td>
							</tr>
					@endforeach
				</tbody>
		</table>
	</div>

	<!-- will be used to show any messages -->
	<div id="message-container"></div>

	<div id="store_information">
		<p><button class="btn btn-default btn-sm" id="back-to-list"><i class="fa fa-arrow-left"></i> Back</button></p>
		<div id="store_info">
		</div>
	</div>
</div><!-- End of container -->
@stop
