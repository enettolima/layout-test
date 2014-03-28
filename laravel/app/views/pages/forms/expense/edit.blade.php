@extends('layouts.default')

@section('content')

<div class="row">
	<div class="col-xs-10">
		<h3>Create Expense Report</h3>
		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
		dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea
		commodo consequat.</p>
	</div>
</div>

<div class="row">
	<div class="col-xs-6">
		<form role="form">
			<div class="form-group">
				<label for="exampleInputEmail1">Employee</label>
				<input type="email" class="form-control" disabled id="exampleInputEmail1" value="<?php echo Auth::user()->fname . " " . Auth::user()->lname ?>">
			</div>
			<div class="form-group">
				<label for="exampleInputEmail1">Date Started</label>
				<input type="text" disabled class="form-control" id="exampleInputEmail1" value="<?php echo Date("r"); ?>">
			</div>
			<div class="form-group">
				<label for="exampleInputEmail1">Title (Optional)</label>
				<input type="text" class="form-control" id="exampleInputEmail1" placeholder="Expense Report #12345">
				<p class="help-block">You can give this expense report a title if you want such as "Vegas Buying Trip"</p>
			</div>
			<h4>Line Items with Receipts</h4>
			<div class="form-group">
				<label for="exampleInputFile">+ Add Receipt</label>
				<input type="file" id="exampleInputFile">
				<p class="help-block">Example block-level help text here.</p>
			</div>
			<div class="checkbox">
				<label>
					<input type="checkbox"> Check me out
				</label>
			</div>
			<button type="submit" class="btn btn-default">Submit</button>
		</form>
	</div>
	<div class="col-xs-6">
	</div>
</div>

@stop
