
function doSearch(searchString){
    var store_id = $("#store-id").val();
    var keyword = $("#search-string").val();
    //$("#item-search>input").attr("disabled", true);
    $("#spinny").show();
    $("#results").html("");

    event.preventDefault();

    var dcs_selected =  $("#folder_selected").val();
  	var current_page = $("#current_page").val();

    $searchRequest = $.ajax({
        method: 'GET',
        url: '/lsvc/restock-search',
        data: {
          'store_id' : store_id,
          'dcs': dcs_selected,
          'keyword' : keyword,
          'page': current_page
        }
    });

    $searchRequest.done(function(response){
        if (response.errors.length>0) {
          //$("#item-search>input").attr("disabled", false).focus();
          showMessage(response.errors[0], 'error');
        }else{

            var resultsHTML = [];
            var options = "";
            for(i = 0; i <= 60; i++) {
              options += "<option>"+i+"</option>";
            }
            var hits = response.data.hits;
            var data = response.data.hits.hits;
            var page_numbers = buildPagination(hits.total);
            if(hits.total>0){
              for(r=0; r<data.length; r++){

                  var result = data[r]._source;
                  var disabled = "disabled";
                  if(result.isavailable==1){
                    disabled = "";
                  }

                  if(result.text4==null){
                    var notes = "";
                  }else{
                    var notes = result.text4;
                  }

                  var realmax = 0;
                  var min     = 0;
                  var max     = 0;
                  var disabled = '';
                  //Checking exception on the max amounts
                  switch (result.minmax[store_id].max) {
                    case "1555":
                      realmax = 500;
                      min = "Undefined";
                      max = "Undefined";
                      break;
                    case "999":
                      min = "Blocked";
                      max = "Blocked";
                      var disabled = 'disabled';
                      realmax = 0;
                      break;
                    default:
                      min = result.minmax[store_id].min;
                      max = result.minmax[store_id].max;
                      realmax = result.minmax[store_id].max;
                      break;
                  }

                  resultsHTML.push("<li class='"+disabled+"'>");
                    resultsHTML.push("<div class='left'>");
                      resultsHTML.push("<div class='compress'>");
                        resultsHTML.push("<img height='160' width='160' src='https://ebapi.earthboundtrading.com/pimg/image/"+result.item_no+"'/>");
                        resultsHTML.push("<i class='fa fa-search-plus' id='img-icon'></i>");
                      resultsHTML.push("</div>");
                      resultsHTML.push("<h3>"+result.description+"</h3>");
                      resultsHTML.push("<p><strong>Item #:</strong> "+result.item_no+" - <strong>Case Quantity:</strong> "+result.case_qty+"<br>");
                      resultsHTML.push("<strong>Desc:</strong> "+result.description+"<br>");
                      resultsHTML.push("<strong>DCS:</strong> "+result.dcs_code+"<br>");
                      resultsHTML.push("<strong>Type:</strong> "+result.type_name+"<br>");
                      resultsHTML.push("<strong>Notes:</strong> "+notes+"</p>");
                    resultsHTML.push("</div>");//End of div left

                    resultsHTML.push("<div class='right'>");
                      resultsHTML.push("<div class='min-max form-inline'>");
                        resultsHTML.push("<strong>Min:</strong> "+min+"<br><strong>Max:</strong> "+max+"<br>");
                      resultsHTML.push("</div>");
                      resultsHTML.push("<div class='qty-form form-inline'>");
                        resultsHTML.push("<div class='form-group'>");
                          resultsHTML.push("<input type='hidden' id='realmax' value='"+realmax+"'>");
                          resultsHTML.push("<label>Cases:&nbsp;</label>");
                          resultsHTML.push("<input type='hidden' id='item_no' value='"+result.item_no+"'>");
                          resultsHTML.push("<select class='add-qty form-control' "+disabled+">"+options+"</select>");
                        resultsHTML.push("</div>");
                      resultsHTML.push("&nbsp;<button type='button' data-item-id='"+result.item_no+"' class='add-to-cart btn btn-default "+disabled+"' >Add</button>");
                    resultsHTML.push("</div>");//End of qty-form
                  resultsHTML.push("</div>");//End of div right
                  resultsHTML.push("</li>");
              }
              $("#results").html(resultsHTML.join(""));
              //$("#results").append(row);
              $(".pagination").html(page_numbers);
            }else{
              showMessage('Product not found! Please try again.', 'error');
            }
        }
        //$("#item-search>input").attr("disabled", false).focus();
        $("#spinny").hide();
    });
}

function buildPagination(totalRecords){
  console.log("Pagination called with "+totalRecords+" records");
	var current_page = $("#current_page").val();
	var pages = Math.ceil(totalRecords/20);
	$("#total_pages").val(pages);
  console.log("pages math is "+pages);
	//alert("number of pages are "+pages);
	var form = '';
	if(pages>1){
    console.log("pages math is "+pages);
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
	}else{
    console.log("inside else");
  }
	return form;
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

$(document).ready(function () {
    //Show bigger image when user clicks on the thumbnail
    $(document).on('click', '#img-icon', function(e) {
      var img       = $(this).prevAll('img').first().attr("src");
      var title     = $(this).parent().siblings('h3').text();
      var item_arr  = $(this).parent().siblings('p').text().split("-");
      var item_no   = item_arr[0];

      $("#message-modal-content").html("<div style='text-align: center;'><img src='"+img+"'/></div>");
      $(".message-modal-title").html(item_no +" - "+ title);
      $('#message-modal').modal('show');
    });


    $("#item-qty>input").on('keydown', function(event){
      if (event.keyCode == 13){
        event.preventDefault();
        //alert( "Handler for click called." );
        updateCartQuantity();
      }
    });

    $(".update-quantity").on('click', function(event){
      //alert( "Handler for click called." );
      updateCartQuantity();
    });

    $(".remove-item").on('click', function(event){
      //alert( "Handler for click called." );
      //console.log("Before fuction -> "+this.className);
      deleteCartProduct(this);
    });

    $(".load-products").on('click', function(event){
      //alert( "Handler for click called." );
      //console.log("Before fuction -> "+this.value);
      //console.log("Before fuction -> "+$(this).next().attr('value'));
      var field_val = $(this).next().attr('value');
      var splt = field_val.split("-");
      getProductsList(splt[0],splt[1]);
    });

    //Change the icon of the text box as the user types
  	$( ".searchfield" ).keyup(function() {
  		toggleSearchButton();
  	});

  	//Execute click when user click on page number on bottom of the page
  	$(document).delegate('.page_number', 'click', function(e){
  		removeActiveFromCurrent();
  		var target = ""+e.target;
      var p_arr = target.split("/");
  		var page = p_arr[4];
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

    //Implement auto-complete
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
  				$.getJSON( '/lsvc/reorder-auto-complete', {
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

    $(document.body).on('click', '.add-to-cart', function(event){
        $(this).attr("disabled", "disabled");
        var qty       = parseInt($(this).closest("div").find(".add-qty").val());
        var item      = $(this).closest("div").find("#item_no").val();
        var max       = parseInt($(this).closest("div").find("#realmax").val());
        var store_id  = $("#store-id").val();

        var debug = "false";
        if(qty > max){
          debug = "true";
        }

        if (parseInt(qty) < 1){
            //alert("Please specify QTY to add to cart.");
            showMessage('Case quantity is required to add a product to the cart! Please try again.', 'error');
        } else {
          if(qty > max){
            showMessage('Case quantity selected is larger than the maximum allowed to your store!', 'error');
          }else{
            var request = $.ajax({
                method : 'POST',
                url: '/lsvc/restock-add-to-cart',
                data: {
                  'store_id' : store_id,
                  'product_id' : item,
                  'quantity' : qty,
                  'max' : max
                }
            });

            request.done(function(response){
              if (response.errors.length>0) {
                $("#item-search>input").attr("disabled", false).focus();
                showMessage(response.errors[0], 'error');
              }else{
                showMessage("Item added to cart successfully.", 'success');
                $("#cart-badge-count").html(response.data.total);
              }
            });
          }
        }
        $(this).attr("disabled", false);
    });


    $(document.body).on('click', '.add-from-archived', function(event){
        //$(this).attr("disabled", "disabled");
        var qty       = parseInt($(this).closest("td").find("#add_qty").val());
        var item      = $(this).closest("td").find("#item_id").val();
        var max       = parseInt($(this).closest("td").find("#realmax").val());
        var store_id  = $("#store-id").val();

        var debug = "false";
        if(qty > max){
          debug = "true";
        }

        if (parseInt(qty) < 1){
            //alert("Please specify QTY to add to cart.");
            showMessage('Case quantity is required to add a product to the cart! Please try again.', 'error');
        } else {
          if(qty > max){
            showMessage('Case quantity selected is larger than the maximum allowed to your store!', 'error');
          }else{
            var request = $.ajax({
                method : 'POST',
                url: '/lsvc/restock-add-to-cart',
                data: {
                  'store_id' : store_id,
                  'product_id' : item,
                  'quantity' : qty,
                  'max' : max
                }
            });

            request.done(function(response){
              if (response.errors.length>0) {
                $("#item-search>input").attr("disabled", false).focus();
                showMessage(response.errors[0], 'error');
              }else{
                showMessage("Item added to cart successfully.", 'success');
                $("#cart-badge-count").html(response.data.total);
              }
            });
          }
        }
        //$(this).attr("disabled", false);
    });

    $("#reset-tree").on('click', function (e) {
      $('#jstree').jstree('close_all');
      updateSelectedFolder("0");
      $('#jstree').jstree("deselect_all");
      doSearch($('.searchfield').val());
      $('.breadcrumb').slideUp();
    });
    //Start jstree on the body load
    if($('#jstree').length > 0) {
      $('#jstree').jstree({
    		'core': {
    			'data': {
    				"url": "/lsvc/dcs-search",
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

      //Execute the search when the documents page is ready
    	doSearch($('.searchfield').val());
    }

    //Process the search
  	$("form").on("submit", function(event){
  		doSearch($('.searchfield').val());
  	});
    //execute document search when the form is submitted
  	$('#search-form').submit(function (e) {
  		e.preventDefault();
  		resetPagination();
  		$('.submit-filter').click();
  	});

  	//Change the folders on jstree to a different icon if open or closed
  	$('#jstree').on('open_node.jstree', function (e, data) { data.instance.set_icon(data.node, "glyphicon glyphicon-folder-open"); });
  	$('#jstree').on('close_node.jstree', function (e, data) { data.instance.set_icon(data.node, "glyphicon glyphicon-folder-close"); });

    $( "#folders" ).change(function() {
  		var fs =  $("#folders").val();
  		resetPagination();
  		updateSelectedFolder(fs);
  		doSearch($('.searchfield').val());
  	});
});

function updateCartQuantity(){
  //console.log( $( "#products-list" ).serialize());
  var myObj = {};
  var rootObj = {};
  var prodObj = {};

  $('form input, form select').each(
    function(index){
      var input = $(this);
      if(input.attr('name')=="store-id"){
        var store_id = input.val();
        //rootObj["store_id"] = store_id;
        myObj["store_id"] = store_id;
      }else{
        prodObj[input.attr('name')] = input.val();
      }
    }
  );

  myObj["products"] = prodObj;
  var json = JSON.stringify(myObj);

  var request = $.ajax({
      method : 'POST',
      url: '/lsvc/restock-update-cart',
      data: {
          'data' : json
      }
  });

  request.done(function(response){
    if (response.errors.length>0) {
      $("#item-search>input").attr("disabled", false).focus();
      showMessage(response.errors[0], 'error');
    }else{
      showMessage("Quantity has been updated.", 'success');
    }
  });
}

function deleteCartProduct(element){
  //Get current title value
  var current_title = $(element).closest('.panel-default').find('.panel-title').find('#collapse-title').text();
  //Break the string so we can calculate the new amount of items on that section
  var arr = current_title.split("-");
  var count = parseInt(arr[1]);
  var new_count = count - 1;
  var new_title = arr[0]+" - "+new_count

  //Build json to send to the LSvcController
  var myObj = {};
  var raw = $(element).attr('data-item-id');
  var arr = raw.split('-');
  myObj["store_id"] = arr[0];
  myObj["item_id"] = arr[1];
  var json = JSON.stringify(myObj);

  var request = $.ajax({
      method : 'DELETE',
      url: '/lsvc/restock-product',
      data: {
          'data' : json
      }
  });

  request.done(function(response){
    if (response.errors.length>0) {
      $("#item-search>input").attr("disabled", false).focus();
      showMessage(response.errors[0], 'error');
    }else{
      showMessage("Item removed successfully.", 'information');
      $("#cart-badge-count").html(response.data.total);
      $(element).closest('.panel-default').find('.panel-title').find('#collapse-title').text(new_title);
      $(element).closest('tr').fadeOut();
    }
  });
}

function showMessage(msgText, msgType){
    var n = noty({
      text: msgText,
      timeout: 3000,
      maxVisible: 2,
      layout: 'topRight',
      type: msgType,
      animation: {
        open: {height: 'toggle'}, // jQuery animate function property object
        close: {height: 'toggle'}, // jQuery animate function property object
        easing: 'swing', // easing
        speed: 500 // opening & closing animation speed
      }
    });
}

function getProductsList(orderId, stage){

  $("#order-products").html("Loading products...");
  var request = $.ajax({
      method : 'GET',
      url: '/lsvc/restock-order-products',
      data: {
          'order_id' : orderId,
          'stage' : stage
      }
  });

  $("#gridSystemModalLabel").html("Order ID: "+orderId);
  request.done(function(response){
    if (response.errors.length>0) {
      $("#item-search>input").attr("disabled", false).focus();
      showMessage(response.errors[0], 'error');
    }else{
      if(response.data.length>0){
        $("#order-products").html("Loading products...");
        var resultsHTML = [];

        for(r=0; r<response.data.length; r++){
          var result = response.data[r];

          var disabled = "disabled";
          if(result.isavailable==1){
            disabled = "";
          }

          switch (result.max_qty) {
            case "1555":
              realmax = 500;
              min = "Undefined";
              max = "Undefined";
              break;
            case "999":
              min = "Blocked";
              max = "Blocked";
              var disabled = 'disabled';
              realmax = 0;
              break;
            default:
              min = result.min_qty;
              max = result.max_qty;
              realmax = result.max_qty;
              break;
          }
          resultsHTML.push("<tr>");
            resultsHTML.push("<td>");
              resultsHTML.push("<img height='50' width='50' class='img-circle img-restock' src='https://ebapi.earthboundtrading.com/pimg/image/"+result.item_no+"'>");
            resultsHTML.push("</td>");
            resultsHTML.push("<td class='prod-description'>");
              resultsHTML.push("<h4>"+result.description1+"</h4>");
              resultsHTML.push("<strong>Item #:</strong> "+result.item_no+"<br>");
              resultsHTML.push("<strong>Boxes:</strong> "+result.item_qty+"<br>");
              resultsHTML.push("<strong>Min:</strong> "+min+" - <strong>Max:</strong> "+max);
              resultsHTML.push("<input type='hidden' id='realmax' value='"+realmax+"'>");
              resultsHTML.push("<input type='hidden' id='item_id' value='"+result.item_no+"'>");
              resultsHTML.push("<input type='hidden' id='add_qty' value='1'>");
              resultsHTML.push("<button type='button' class='add-from-archived btn btn-default' "+disabled+"'>Add to current cart</button>");
            resultsHTML.push("</td>");
          resultsHTML.push("</tr>");
        }
        $("#order-products").html(resultsHTML.join(""));
      }
    }
  });
}

/*
 * Function to add the folder selected on the dom to be sent when executing the search
 */
function updateSelectedFolder(folder){
	$("#folder_selected").val(folder);
}
