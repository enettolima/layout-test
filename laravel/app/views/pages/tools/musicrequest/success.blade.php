@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-8">
        <h3>Thank You for Your Input</h3>
        <p>Your request has been logged and sent to those responsible for making requests to Mood Media.</p>

		<h4>Your Request Details:</h4>
		<dl>
			<dt>Requestor</dt>
			<dd>
				{{ Session::get('lastMusicRequest')['empid'] }}
				{{ Session::get('lastMusicRequest')['empname'] }}
			</dd>
		</dl>
		<dl>
			<dt>Request</dt>
			<dd class="well">
				{{ nl2br(Session::get('lastMusicRequest')['request']) }}
			</dd>
		</dl>

        <p>Your suggestions are greatly appreciated, thank you for helping to improve the in-store music!</p>

    </div>
</div>

@stop
