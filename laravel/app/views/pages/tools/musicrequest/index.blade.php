@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-6">
        <h3>Music Request Form</h3>

		@if ($errors->has())
			@include('includes.errors')
		@endif

        <p>
			Do you have any suggestions for the in-store music? Please
			provide your suggestions via the form below. You can request
			songs to be added, songs to be deleted, make general
			suggestions, etc. 
			
			<blockquote>Tip: the more specific your request, the better!</blockquote>
		</p>

		<h5>Examples of Helpful Requests:</h5>
		<ul>
			<li>"Please see if you can add 'Song X' by Artist X, 'Song Y' by Artist Y, and 'Song Z' by Artist Z"</li>
			<li>"'Song B' by Artist B has three curse words in it and should be removed!"</li>
			<li>"The following three songs are too quiet/boring/loud/etc."</li>
		</ul>

		<!--
		<h5>Examples of Unhelpful Requests:</h5>
		<ul>
			<li>"Modern music upsets me."</li>
			<li>"Old music upsets me."</li>
		</ul>
		-->

		<p>
			Your suggestions are <strong>GREATLY</strong> appreciated!
		</p>

        <form role="form" class="form" method="POST">
            <div class="form-group">
				<textarea rows="10" class="form-control" name="request" placeholder="Make a request or suggestion here!"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Music Request</button>
        </form>
        <br />
        <span id="searching" class="hidden"><em>Searching </em><img src="/images/ajax-loader-arrows.gif"></span>
        <blockquote id="results" class="hidden"></blockquote>
    </div>
    <div class="col-xs-5 col-xs-offset-1">
        <h4>Your Requests</h4>
        <ul>
            @foreach ($userRequests as $request)
                <li>
                    <a href="/tools/music-request-feedback/{{ $request->id }}">{{ date("m/d/y g:ia", strtotime($request->created_at)) }}</a>
					{{ $request->comments()->count() > 0 ? ' - <strong>' . $request->comments()->count() . ' Response(s)</strong>' : '' }}
                </li>
            @endforeach
        </ul>
    </div>

</div>

@stop
