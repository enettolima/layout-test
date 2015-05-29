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

	var data = {
		folder: folder_selected,
		keyword: searchstring
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
			var doc_ids = [];
			var source = null;
			var content = '';

			if (data.length > 0) {
				$("#resultsHeader").html(data.length + " Results").show();
				for (var i = 0; i < data.length; i++) {
					source = data[i]._source.file;
					var url = source.url;
					//console.log("id is "+data[i]._id+" and url is "+url);
					var dateString = undefined;

					if (typeof data[i]._source.meta.date !== "undefined") {
						var formattedDate = new Date(data[i]._source.meta.date);
						var d = formattedDate.getDate();
						var m =  formattedDate.getMonth();
						m += 1;  // JavaScript months are 0-11
						var y = formattedDate.getFullYear();
						dateString = m + "/" + d + "/" + y;
					}
					var re = /^file:\/\/\/media\/web\/downloads\/(.*)$/;
					if (url) {
						var highlight = "";
						if (data[i].highlight) {
							highlight = data[i].highlight.content[0];
					 	}

						var fixed = url.match(re)[1];
						var filename 	= source.filename;
						var virtual 	= data[i]._source.path.virtual;
						var full = "/docs" + virtual + encodeURIComponent(filename);
						var row = "";
						row += "<li>";
						row += "<h4><a target='_blank' href='"+full+"'>"+filename+"</a></h4>";
						row += "<ul>";
						if (typeof dateString !== "undefined") {
							row += "<li><strong>File Date:</strong> "+dateString+"</li>";
						}
						row += "<li>"+highlight+"</li>";
						row += "<li class='file-path'><i class='fa fa-paper-plane-o'></i> Path: "+virtual+"</li>";
						row += "</ul>";
						row += "</li>";
						$("#results").append(row);
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

	//If button is clicked when there is text in it,
	//clear text and show folders
	$(".input-group-addon").on('click', function (e) {
		if ($('.searchfield').val()) {
			$('.searchfield').val("");
			toggleSearchButton();
		}
		doSearch($('.searchfield').val());
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
			doSearch($('.searchfield').val());
		}
	});

	//Change the folders on jstree to a different icon if open or closed
	$('#jstree').on('open_node.jstree', function (e, data) { data.instance.set_icon(data.node, "glyphicon glyphicon-folder-open"); });
	$('#jstree').on('close_node.jstree', function (e, data) { data.instance.set_icon(data.node, "glyphicon glyphicon-folder-close"); });

	//execute document search when the form is submitted
	$('#search-form').submit(function (e) {
		e.preventDefault();
		$('.submit-filter').click();
	});

	$( "#folders" ).change(function() {
		var fs =  $("#folders").val();
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