@extends('layouts.default')

@section('content')

<div class="doc-search">

	<h3>Earthbound Documents</h3>

	<form role="form" method="GET">
		<!--<div class="form-group">-->
		<div class="input-group">
			<input value="<?php echo Input::get('search'); ?>" type="text" class="form-control searchfield"
				name="search" id="search" placeholder="Search Documents" autofocus>
			<span class="input-group-addon">
					<i class="fa fa-search" id="textbox-icon"></i>
			</span>
		</div><!-- End of input-group -->
		<input val="Search" type="submit" class="btn btn-default hidden">&nbsp;
	</form>
	<div id="results-found">
	</div>
	<!--Breadcrumb container-->
	<div class="breadcrumb-container">
		<ul class = "breadcrumb" >
			<li>
				<a>/Root</a>
			</li>
		</ul>
	</div>
	<!--Container for the tree view and results-->
	<div class = "row clearfix tree-container" >
		<div class = "col-md-8 column" >
				<div id="error-container">
				</div>
				<div class = "col-md-14" >
						<div class = "row-fluid" >
								<div class = "results span11" >
										<input type="submit" class="btn btn-default submit-filter hidden" >
										<input type="hidden" id="folder_selected" name="folder_selected" >
								</div >
								<span style="display:none;" id="spinny">
									<img height="52" width="52" src="/images/spinner.gif">
								</span>
								<ul id="results">
								</ul>
						</div >
				</div >
		</div >
		<div class = "col-md-4 column well" >
			<h5 class="filterTitle">Filter</h5>
				<input class="btn btn-default" type="button" value="Reset" id="reset-tree">
				<div id="jstree">
				</div >
		</div >
		<div class = "col-md-12 column" >
		</div >
	</div ><!-- End of row clearfix container -->
	<nav>
		<input type="hidden" id="current_page" value="1">
		<input type="hidden" id="total_pages" value="1">
	  <ul class="pagination">
	  </ul>
	</nav>
</div><!-- End of doc-search -->
@stop
