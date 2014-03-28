@extends('layouts.default')

@section('content')


<div class="row">
	<div class="col-xs-12">
		<h3>Expense Reports</h3>
		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
		dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea
		commodo consequat.</p>
		<a href="/forms/expense-report-new" class="btn btn-large btn-primary">Create New Expense Report</a>
		<hr>
	</div>
</div>

<div class="row">
	<div class="col-xs-6">
		<h4>Unfinished Reports</h4>
		<p>Reports in progress that you need to finish.</p>
		<ul style="line-height:45px;" class="list-unstyled">
			<li><a href="" class="btn btn-info">Photo Shoot Expenses - Fri Mar 28, 2014</a> - <small><em>Saved Yesterday 5:06pm</em></small></li>
			<li><a href="" class="btn btn-info">Trip to Vegas - Thu Mar 27, 2014</a> - <small><em>Saved Monday 3:06pm</em></small></li>
		</ul>
	</div>
	<div class="col-xs-6">
		<h4>Submitted Reports</h4>
		<p>Reports in progress that you need to finish.</p>
		<ul style="line-height:45px;" class="list-unstyled">
			<li><a href="" class="btn btn-warning">Trip to Vegas - Thu Mar 27, 2014</a> - <small><em>Waiting Approval</em></small></li>
			<li><a href="" class="btn btn-success">Business Lunch - Fri Mar 28, 2014</a> - <small><em>Approved 1/2/2014</em></small></li>
			<li><a href="" class="btn btn-success">Trip to the Store - Mon Feb 28, 2014</a> - <small><em>Approved 1/2/2014</em></small></li>
		</ul>
	</div>
</div>

@stop
