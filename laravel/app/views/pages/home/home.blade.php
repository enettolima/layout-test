@extends('layouts.default')

@section('content')

<script src="/js/Chart.js/Chart.js"></script>

<div class="jumbotron">

    <h3>Welcome to EBT Passport, {{ Auth::user()->full_name }}</h3>
    <p>
        You are logged in as {{ Auth::user()->username }}. {{ HTML::link('users/logout', '[Logout]') }}
    </p>
</div>



<div class="row">
    <div class="col-xs-8">
		<h3>Arbitrary Chart</h3>
		<canvas id="myChart" width="665" height="303"></canvas>
		<script src="/js/hpchart.js" type="text/javascript" charset="utf-8"></script>
    </div>
    <div class="col-xs-4">
		<h3>Document Feed</h3>
		<p>Here are the last 5 document updates:</p>
		<ul>
			<li><a href=""><strong>One Voice</strong> &mdash; <?php echo date("D, M d");?></a></li>
			<li><a href=""><strong>One Voice</strong> &mdash; <?php echo date("D, M d", strtotime('yesterday'));?></a></li>
			<li><a href=""><strong>Merchandise Alert</strong> &mdash; <?php echo date("D, M d", strtotime('last monday'));?></a></li>
			<li><a href=""><strong>Employee Handbook</strong> &mdash; <?php echo date("D, M d", strtotime('1/1/2014'));?></a></li>
			<li><a href=""><strong>One Voice</strong> &mdash; <?php echo date("D, M d", strtotime('yesterday'));?></a></li>
		</ul>
		<p>
			<h3>Here is some News:</h3>
			<img class="img-responsive img-rounded pull-right" src="/images/jpeg.jpg">
			<strong>Lorem ipsum dolor sit amet</strong>, consectetur adipisicing elit, sed do eiusmod tempor
			incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
			nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
			Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
			fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
			culpa qui officia deserunt mollit anim id est laborum.
		</p>
    </div>
</div>

<div class="row">
	<div class="col-xs-8">
	</div>
</div>

@stop
