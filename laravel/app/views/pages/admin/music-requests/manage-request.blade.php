@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-10">
        <h3>Music Request Management</h3>

		<section>
			<h4>Request</h4>
			<!-- First Comment -->

			<header class="text-left">
				<div class="comment-user"><i class="fa fa-user"></i> {{ $req->empid }} {{ $req->empname }}</div>
				<time><i class="fa fa-clock-o"></i> {{ Carbon::parse($req->created_at)->toDayDateTimeString() }}</time>
			</header>

			<div class="comment-post">
				<p>{{ nl2br($req->request) }}</p>
			</div>

		</section>

		<form class="form" role="form" method="POST">
			<div class="form-group">
				<textarea class="form-control" rows="10" name="comment">{{ $comment }}</textarea>
			</div>
			<div class="checkbox">
				<input {{ $req->closed_at != "" ? "checked" : "" }} type="checkbox" name="close_request"> Close Request
			</div>
			<input type="hidden" name="request-id" value="{{ $req->id }}">
			<button type="submit" class="btn btn-default">Submit</button>
		</form>

    </div>
</div>

@stop
