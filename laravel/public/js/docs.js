/*
 * Function to make search on elasticsearch file system river
 */
function doSearch(searchstring) {
	$("#spinny").show();

	$("#resultsHeader").hide();
	hideErrorMessage("#error-container");

	event.preventDefault();

	$("#results").empty();

	var folder_selected =  $("#folder_selected").val();
	var current_page = $("#current_page").val();

	var data = {
		folder: folder_selected,
		keyword: searchstring,
		page: current_page
	};

	$.ajax({
		url: '/lsvc/docs-search',
		//url: '/modules/search.php',
		type: 'POST',
		contentType: 'application/json; charset=UTF-8',
		crossDomain: true,
		dataType: 'json',
		data: JSON.stringify(data),
		success: function(response) {

			$("#spinny").hide();

			var data = response.hits.hits;
			var hits = response.hits;
			var doc_ids = [];
			var source = null;
			var content = '';

			if (data.length > 0) {

				var page_numbers = buildPagination(hits.total);
				//$("#resultsHeader").html(data.length + " Results").show();
				$("#results-found").html("Showing "+data.length+" of "+hits.total+" ("+response.took/1000+" seconds)");
				for (var i = 0; i < data.length; i++) {
					source = data[i]._source;
					var url = source.full_path;
					//console.log("id is "+data[i]._id+" and url is "+url);
					var date = source.updated_at;

					var arr = date.split(/-|\s|:/);// split string and create array.
					var dateString = new Date(arr[0], arr[1] -1, arr[2], arr[3], arr[4], arr[5]);
					/*if (typeof data[i]._source.meta.date !== "undefined") {
						var formattedDate = new Date(data[i]._source.meta.date);
						var d = formattedDate.getDate();
						var m =  formattedDate.getMonth();
						m += 1;  // JavaScript months are 0-11
						var y = formattedDate.getFullYear();
						dateString = m + "/" + d + "/" + y;
					}*/
					var re = /^file:\/\/\/media\/web\/downloads\/(.*)$/;
					if (url) {
						var highlight = "";
						var highlight_row = "";
						if (data[i].highlight) {
							//If we want to enable all the rows for the highlight
							//for(var x = 0; x<data[i].highlight.content.length; x++){
							for(var x = 0; x<1; x++){
								highlight_row += "<li>..."+data[i].highlight.content[x]+"...</li>";
							}
					 	}

						//var fixed = url.match(re)[1];
						var filename 	= source.name;
						var virtual 	= data[i]._source.full_path;//.replace("/media","");
						var clicks		= data[i]._source.clicks;
						var id 	= data[i]._id;//.replace("/media","");
						//Production
						var full = "https://ebtpassport.com/documents/load-file?filename="+filename+"&path="+virtual+"&id="+id;
						//Local Dev
						//var full = "http://newdev.ebtpassport.com/documents/load-file?filename="+filename+"&path="+virtual+"&id="+id;
						var row = "";
						row += "<li>";
						row += "<h4><a target='_blank' href='"+full+"'>"+filename+"</a></h4>";
						row += "<ul>";
						if (typeof dateString !== "undefined") {
							row += "<li class='file-path'><strong>File Date:</strong> "+dateString+"</li>";
						}
						row += "<li class='file-path'> Path: "+virtual+"</li>";
						//row += "<li class='file-path'> Clicks: "+clicks+"</li>";
						//row += "<li>"+highlight+"</li>";
						row += highlight_row;
						row += "</ul>";
						row += "</li>";
						$("#results").append(row);
						$(".pagination").html(page_numbers);
					}
				}
			} else {
				//$("#resultsHeader").html("No Results").show();
				showErrorMessage("#error-container", "Document not found! Please try again.","alert-danger", true, 2000);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			var jso = jQuery.parseJSON(jqXHR.responseText);
			error_note('section', 'error', '(' + jqXHR.status + ') ' + errorThrown + ' --<br />' + jso.error);
		}
	});
}

function buildPagination(totalRecords){

	var current_page = $("#current_page").val();
	var pages = Math.ceil(totalRecords/20);
	$("#total_pages").val(pages);
	//alert("number of pages are "+pages);
	var form = '';
	if(pages>1){
		var next;
		var prev;

		if(current_page==1){
			prev = 'class="disabled"';
		}
		form = '<li '+prev+' ><a href="#" onclick="return false;" class="page_previous"><span aria-hidden="true">&laquo;</span></a></li>';
		for (var i = 0; i < pages; i++) {
			//array[i]
			var pg = i + 1;
			var sel = ""
			if(pg==current_page){
				sel = 'class="active"';
			}
			form += '<li '+sel+' id="pg-'+pg+'"><a href="'+pg+'" onclick="return false;" class="page_number">'+ pg +'</a></li>';
		}
		if(current_page==pages){
			next = 'class="disabled"';
		}
		form += '<li '+next+' ><a href="#" onclick="return false;" class="page_next"><span aria-hidden="true">&raquo;</span></a></li>';
	}
	return form;
}
/*
 * Function to show and hide the error container
 */
function showErrorMessage(container, message, type, auto_hide, hide_timeout){
	var error_box = '<div class="alert '+type+' alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+message+'</div>';
	$(container).html(error_box);
	$(container).hide();
	$(container).slideDown("fast", function() {});

	if(auto_hide){
		$(container).delay(hide_timeout).slideUp();
	}
}

function hideErrorMessage(container){
	//$(container).html('');
	$(container).slideUp("fast", function() {});
	//$(container).hide();
}

function resetPagination(){
	removeActiveFromCurrent()
	var current_page = $("#current_page").val();
	$("#current_page").val(1);
	$('#pg-'+current_page).toggleClass( "active" );
}

function removeActiveFromCurrent(){
	var current_page = $("#current_page").val();
	$('#pg-'+current_page).removeClass( "active" );
}
/*
 * Function toggles between the search results and the folders structure
 */
function toggleSearchButton(){
	if ($('.searchfield').val()) {
		//Change the icon to show the cross instead of the search icon
		$('#textbox-icon').removeClass('fa fa-search').addClass('fa fa-times');
	}else{
		$('#results').html('');
		//Change the icon to show the search instead of the cross
		$('#textbox-icon').removeClass('fa fa-times').addClass('fa fa-search');
	}
}

$(function () {
	//Change the icon of the text box as the user types
	$( ".searchfield" ).keyup(function() {
		toggleSearchButton();
	});

	//Execute click when user click on page number on bottom of the page
	$(document).delegate('.page_number', 'click', function(e){
		removeActiveFromCurrent();
		var target = ""+e.target;
		var p_arr = target.split("/");
		var page = p_arr[3];
		$('html, body').animate({scrollTop: '0px'}, 0);
		$( this ).parent().toggleClass( "active" );
		$("#current_page").val(page);
		doSearch($('.searchfield').val());
	});

	//Click on previous button on pagination
	$(document).delegate('.page_previous', 'click', function(e){
		removeActiveFromCurrent();
		var current_page = $("#current_page").val();
		if(current_page>1){
			$('html, body').animate({scrollTop: '0px'}, 0);
			var prev = current_page - 1;
			$('#pg-'+prev).toggleClass( "active" );
			$("#current_page").val(prev);
			doSearch($('.searchfield').val());
		}
	});

	//Click on next button on pagination
	$(document).delegate('.page_next', 'click', function(e){
		removeActiveFromCurrent();
		var current_page = $("#current_page").val();
		var total_pages = $("#total_pages").val();
		if(parseInt(current_page)<total_pages){
			$('html, body').animate({scrollTop: '0px'}, 0);
			var next = parseInt(current_page) + 1;
			$('#pg-'+next).toggleClass( "active" );
			$("#current_page").val(next);
			doSearch($('.searchfield').val());
		}
	});

	//If button is clicked when there is text in it,
	//clear text and show folders
	$(".input-group-addon").on('click', function (e) {
		if ($('.searchfield').val()) {
			$('.searchfield').val("");
			toggleSearchButton();
		}
		doSearch($('.searchfield').val());
	});

	function split( val ) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}

	$( ".searchfield" )
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === $.ui.keyCode.TAB &&
					$( this ).autocomplete( "instance" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			source: function( request, response ) {
				$.getJSON( '/lsvc/auto-complete', {
					term: extractLast( request.term )
				}, response );
			},
			search: function() {
				resetPagination();
				// custom minLength
				var term = extractLast( this.value );
				if ( term.length < 2 ) {
					return false;
				}
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				resetPagination();
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				terms.push( "" );
				//this.value = terms.join( ", " );
				this.value = terms.join( " " );
				doSearch(this.value);
				return false;
			}
		});

	//Execute the search when the documents page is ready
	doSearch($('.searchfield').val());

	//Process the search
	$("form").on("submit", function(event){
		doSearch($('.searchfield').val());
	});
	$("#reset-tree").on('click', function (e) {
		$('#jstree').jstree('close_all');
		updateSelectedFolder("0");
		$('#jstree').jstree("deselect_all");
		doSearch($('.searchfield').val());
		$('.breadcrumb').slideUp();
	});

	//To hide the breadcrumb on the first load
	$('.breadcrumb').hide();

	//Start jstree on the body load
	$('#jstree').jstree({
		'core': {
			'data': {
				"url": "/lsvc/folder-search",
				'data' : function (node) {
					return { 'id' : node.id };
				}
			}
		},
		"types" : {
			"root" : {
				"icon" : "glyphicon glyphicon-flash",
				"valid_children" : ["default"]
			},
			"default" : {
				"icon" : "glyphicon glyphicon-folder-close",
				"valid_children" : ["default","file"]
			},
			"file" : {
				"icon" : "glyphicon glyphicon-file",
				"valid_children" : []
			}
		},
		"plugins" : [
			"contextmenu", "sort", "types", "wholerow"
		]
	}).on("changed.jstree", function (e, data) {
		if (data.selected.length) {
			$('#path').val(data.instance.get_node(data.selected[0]).id);
			updateSelectedFolder(data.instance.get_node(data.selected[0]).id);
			$('.breadcrumb li a').text(data.instance.get_node(data.selected[0]).id);
			$('.breadcrumb').slideDown();
			//$('#search-form').submit();
			resetPagination();
			doSearch($('.searchfield').val());
		}
	});

	//Change the folders on jstree to a different icon if open or closed
	$('#jstree').on('open_node.jstree', function (e, data) { data.instance.set_icon(data.node, "glyphicon glyphicon-folder-open"); });
	$('#jstree').on('close_node.jstree', function (e, data) { data.instance.set_icon(data.node, "glyphicon glyphicon-folder-close"); });

	//execute document search when the form is submitted
	$('#search-form').submit(function (e) {
		e.preventDefault();
		resetPagination();
		$('.submit-filter').click();
	});

	$( "#folders" ).change(function() {
		var fs =  $("#folders").val();
		resetPagination();
		updateSelectedFolder(fs);
		doSearch($('.searchfield').val());
	});
});

/*
 * Function to add the folder selected on the dom to be sent when executing the search
 */
function updateSelectedFolder(folder){
	$("#folder_selected").val(folder);
}
