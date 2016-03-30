
function doItemSearch(searchString){

    resetMessaging();

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

function showMessage(msgText, msgType){

    var msgClass;

    switch (msgType) {
        case 'success':
            msgClass = 'bg-success';
            break;

        case 'error':
            msgClass = 'bg-danger';
            break;

        case 'info':
        default:
            msgClass = 'bg-info';
            break;
    }

    $("#restock-message").removeClass(function (index, css) {
        return (css.match (/(^|\s)bg-\S+/g) || []).join(' ');
    });

    $("#restock-message").addClass(msgClass).html(msgText).show();
}

function resetMessaging() {
    $("#restock-message").html("").hide();
}

$(document).ready(function () {

    $("#item-search>input").on('keydown', function(event){

        resetMessaging();

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
      updateCartQuantity()
    });

    $(document.body).on('click', '.add-to-cart', function(event){

        resetMessaging();

        $(this).attr("disabled", "disabled");
        var qty = $(this).closest("div").find(".add-qty").val();
        var item = $(this).closest("div").find("#item_no").val();
        var store_id = $("#store-id").val();

        if (parseInt(qty) < 1){
            alert("Please specify QTY to add to cart.");
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
                showMessage("Item was added to cart.", 'success');
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
  //alert(json);

  console.log("products are "+json);
  var request = $.ajax({
      method : 'POST',
      url: '/lsvc/restock-update-cart',
      data: {
          'data' : json
      }
  });

  //request.done(function(response){
    /*if (response.errors.length>0) {
      $("#item-search>input").attr("disabled", false).focus();
      showMessage(response.errors[0], 'error');
    }else{
      console.log("Data received total was "+response.data.total);
      showMessage("Item was added to cart.", 'success');
      //$("#cart-badge-count").html(response.data.total);
    }*/
  //});
}
