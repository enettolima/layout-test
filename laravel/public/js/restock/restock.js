
function doItemSearch(searchString){
    var store_id = $("#store-id").val();
    var keyword = $("#search-string").val();
    $("#item-search>input").attr("disabled", true);
    $("#spinny").show();
    $("#results").html("");
    //console.log("keyword is "+keyword+" and store id "+store_id);
    $searchRequest = $.ajax({
        method: 'GET',
        url: '/lsvc/dev-restock-search',
        data: {
          'store_id' : store_id,
          'keyword' : keyword
        }
    });

    $searchRequest.done(function(response){
        if (response.errors.length>0) {
          $("#item-search>input").attr("disabled", false).focus();
          showMessage(response.errors[0], 'error');
        }else{
            var resultsHTML = [];
            var options = "";
            for(i = 0; i <= 60; i++) {
              options += "<option>"+i+"</option>";
            }
            for(r=0; r<response.data.length; r++){

                var result = response.data[r];
                var disabled = "disabled";
                if(result.isavailable==1){
                  disabled = "";
                }
                resultsHTML.push("<tr>");
                    resultsHTML.push("<td>");
                        resultsHTML.push("<img height='120' width='120' class='img-circle' src='https://ebapi.earthboundtrading.com/pimg/image/"+result.item_no+"'>");
                    resultsHTML.push("</td>");

                    resultsHTML.push("<td class='prod-description'>");
                        resultsHTML.push("<h4>"+result.description+"</h4>");
                        resultsHTML.push("<ul>");
                            resultsHTML.push("<li><strong>Item #:</strong> "+result.item_no+"</li>");
                            resultsHTML.push("<li><strong>Desc:</strong> "+result.description+"</li>");
                            resultsHTML.push("<li><strong>HQ Quantity:</strong> "+result.hq_qty+"</li>");
                            resultsHTML.push("<li><strong>Case Quantity:</strong> "+result.case_qty+"</li>");
                        resultsHTML.push("</ul>");

                        resultsHTML.push("<div class='qty-form form-inline'>");
                            resultsHTML.push("<div class='form-group'>");
                                resultsHTML.push("<label>QTY:&nbsp;</label>");
                                resultsHTML.push("<input type='hidden' id='item_no' value='"+result.item_no+"'>");
                                resultsHTML.push("<select class='add-qty form-control' "+disabled+">");
                                  resultsHTML.push(options);
                                resultsHTML.push("</select>");
                        resultsHTML.push("</div>");


                        resultsHTML.push("&nbsp;<button type='button' data-item-id='"+result.item_no+"' class='add-to-cart btn btn-default "+disabled+"' >Add to Cart</button>");
                        resultsHTML.push("</div>");
                    resultsHTML.push("</td>");
                resultsHTML.push("</tr>");

            }
            $("#results").html(resultsHTML.join(""));
        }

        $("#item-search>input").attr("disabled", false).focus();
        $("#spinny").hide();
    });
}



$(document).ready(function () {

    $("#item-search>input").on('keydown', function(event){

        if (event.keyCode == 13){

            $(this).attr('disabled', 'disabled');

            doItemSearch($(this).val());

        }

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
      console.log("Before fuction -> "+this.className);
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

    $(document.body).on('click', '.add-to-cart', function(event){
        $(this).attr("disabled", "disabled");
        var qty = $(this).closest("div").find(".add-qty").val();
        var item = $(this).closest("div").find("#item_no").val();
        var store_id = $("#store-id").val();

        if (parseInt(qty) < 1){
            //alert("Please specify QTY to add to cart.");
            showMessage('Quantity is required to add a product to the cart! Please try again.', 'error');
        } else {
            var request = $.ajax({
                method : 'POST',
                url: '/lsvc/dev-restock-add-to-cart',
                data: {
                    'store_id' : store_id,
                    'product_id' : item,
                    'quantity' : qty
                }
            });

            request.done(function(response){
              if (response.errors.length>0) {
                $("#item-search>input").attr("disabled", false).focus();
                showMessage(response.errors[0], 'error');
              }else{
                console.log("Data received total was "+response.data.total);
                showMessage("Item added to cart successfully.", 'success');
                $("#cart-badge-count").html(response.data.total);
              }
            });
        }
        $(this).attr("disabled", false);
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
      console.log('Type: ' + input.attr('type') + ' Name: ' + input.attr('name') + 'Value: ' + input.val());
      //var nm = input.attr('name');
      //var arr = data.split('[');
    }
  );

  myObj["products"] = prodObj;
  var json = JSON.stringify(myObj);

  console.log("products are "+json);
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
  console.log("Clicked button class -> "+element.className);
  //console.log("Closest element -> "+$(element).prev('input').attr('id'));
  console.log("Closest element -> "+$(element).attr('data-item-id'));

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
      console.log("Data received total was "+response.data.total);
      showMessage("Item removed successfully.", 'information');
      $("#cart-badge-count").html(response.data.total);

      $(element).closest('tr').fadeOut();
    }
  });
}

function showMessage(msgText, msgType){
    var n = noty({
      text: msgText,
      timeout: 2000,
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
      //console.log("Data received total was "+response.data.total);
      //showMessage("Item removed successfully.", 'information');
      //$("#cart-badge-count").html(response.data.total);

      //$(element).closest('tr').fadeOut();
      if(response.data.length>0){
        $("#order-products").html("Loading products...");
        var resultsHTML = [];

        for(r=0; r<response.data.length; r++){
          var result = response.data[r];
          console.log("Item ID is "+result.item_no);
          resultsHTML.push("<tr>");
            resultsHTML.push("<td>");
              resultsHTML.push("<img height='50' width='50' class='img-circle img-restock' src='https://ebapi.earthboundtrading.com/pimg/image/"+result.item_no+"'>");
            resultsHTML.push("</td>");
            resultsHTML.push("<td class='prod-description'>");
              resultsHTML.push("<h4>"+result.description+"</h4>");
              resultsHTML.push("<strong>Item #:</strong> "+result.item_no+"<br>");
              resultsHTML.push("<strong>Boxes:</strong> "+result.item_qty);
            resultsHTML.push("</td>");
          resultsHTML.push("</tr>");
        }
        $("#order-products").html(resultsHTML.join(""));
        //$("#order-products").html(row);
      }
    }
  });
}
