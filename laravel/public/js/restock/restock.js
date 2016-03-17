
function doItemSearch(searchString){

    resetMessaging();

    $("#item-search>input").attr("disabled", true);
    $searchRequest = $.ajax({
        method: 'GET',
        url: '/lsvc/dev-restock-search',
        data: {
            foo : 'bar'
        }
    });

    $searchRequest.done(function(response){

        if (response.errors) {
            showMessage(response.errors[0].title, 'error');
        }else{
            var resultsHTML = [];

            for(r=0; r<response.data.length; r++){

                var result = response.data[r];

                resultsHTML.push("<tr>");
                    resultsHTML.push("<td>");
                        resultsHTML.push("<img height='120' src='https://ebapi.earthboundtrading.com/pimg/image/33709'>");
                    resultsHTML.push("</td>");

                    resultsHTML.push("<td>");
                        resultsHTML.push("<h4>"+result.name+"</h4>");
                        resultsHTML.push("<ul>");
                            resultsHTML.push("<li><strong>Item #:</strong> "+result.id+"</li>");
                            resultsHTML.push("<li><strong>Desc:</strong> "+result.text+"</li>");
                        resultsHTML.push("</ul>");

                        resultsHTML.push("<div class='qty-form form-inline'>");
                            resultsHTML.push("<div class='form-group'>");
                                resultsHTML.push("<label>QTY:&nbsp;</label>");
                                resultsHTML.push("<select class='add-qty form-control'>");
                                    resultsHTML.push("<option>0</option>");
                                    resultsHTML.push("<option>1</option>");
                                    resultsHTML.push("<option>2</option>");
                                    resultsHTML.push("<option>3</option>");
                                resultsHTML.push("</select>");
                        resultsHTML.push("</div>");
                        resultsHTML.push("&nbsp;<button type='button' data-item-id='"+result.id+"' class='add-to-cart btn btn-default'>Add to Cart</button>");
                        resultsHTML.push("</div>");
                    resultsHTML.push("</td>");
                resultsHTML.push("</tr>");

            }

            $("#results").html(resultsHTML.join(""));

        }

        $("#item-search>input").attr("disabled", false).focus();

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

    $(document.body).on('click', '.add-to-cart', function(event){

        resetMessaging();

        $(this).attr("disabled", "disabled");
        var qty = $(this).closest("div").find(".add-qty").val();


        if (parseInt(qty) < 1){
            alert("Please specify QTY to add to cart.");


        } else {
            var request = $.ajax({
                method : 'POST',
                url: '/lsvc/dev-restock-add-to-cart',
                data: {
                    foo : 'bar'
                }
            });

            request.done(function(data){
                showMessage("Item was added to cart.", 'success');
            });

        }

        $(this).attr("disabled", false);


    });

});






























