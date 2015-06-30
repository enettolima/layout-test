@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-6">
        <h3>Music Request Feedback</h3>

		<section>
			<h4>Your Request</h4>
			<!-- First Comment -->

			<header class="text-left">
				<time><i class="fa fa-clock-o"></i> {{ Carbon::parse($request->created_at)->toDayDateTimeString() }}</time>
			</header>

			<div class="comment-post">
				<p>{{ nl2br($request->request) }}</p>
			</div>

		</section>

		<section>
			<h4>Comments</h4>

            @foreach ($request->comments as $comment)
			<header class="text-left">
				<div class="comment-user"><i class="fa fa-user"></i> {{ $comment->commenter_full_name }}</div>
				<time class="comment-date" datetime="16-12-2014 01:05"><i class="fa fa-clock-o"></i> {{ Carbon::parse($comment->created_at)->toDayDateTimeString() }}</time>
			</header>

			<div class="comment-post">
				<p>{{ nl2br($comment->comment) }}</p>
			</div>
			@endforeach

		</section>

	</div>

</div>

@stop
