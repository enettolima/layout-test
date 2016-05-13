@extends('layouts.default')

@section('content')

@include('pages.restock.restockheader')
<div id="content-restock">
  @if ($error)
    <div id="restock-message" style="" class="bg-danger">{{$error_msg}}</div>
  @endif

@include('pages.restock.restocknav')

    <!--Search field-->
    <form role="form" method="GET">
  		<div class="input-group" id="item-search">
  			<input value="<?php echo Input::get('search-string'); ?>" type="text" class="form-control input-lg searchfield"
  				name="search-string" id="search-string" placeholder="Search Products" autofocus>
  			<span class="input-group-addon">
  					<i class="fa fa-search" id="textbox-icon"></i>
  			</span>
        <input type="hidden" id="store-id" value={{$store_id}}>
  		</div><!-- End of input-group -->
    </form>
    <div id="restock-message" style="display:none;" class="alert alert-error"></div>

    <div id="results-found">
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
    <!-- Modal Block -->
    <div id="message-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="message-modal-title">Title</h4>
                </div>
                <div id="message-modal-content" class="modal-body"></div>
                <div class="modal-footer">
                    <button id="message-confirm" type="button" data-event-id="" data-dismiss="modal" class="btn">Close</button>
                </div>
            </div>
        </div>
    </div><!-- End Modal Block -->
</div>
@stop
