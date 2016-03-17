var currentWeekOf = '2016-03-17';

$(document).ready(function(){

    fetchItems(currentWeekOf);

    $("#weborder-save").on('click', function(event){
        save();
    });

    $("#add-row").on('click', function(event){
        addEmptyRow();
    });

    $(document.body).on('click', '.weborder-edit', function(e){
        $(this).parent("div").find("#item_id").attr("disabled", false);
        $(this).parent("div").find("#item_qty").attr("disabled", false);
    });

    $(document.body).on('click', '.weborder-delete', function(e){

        if (confirm("Are you sure you want to delete this item?")){
            $(this).parent("div").remove();
        }
    });

});


function save()
{
    var items = [];

    $(".web-order-row").each(function(row){

        var item_id  = $(this).find("#item_id").val();
        var item_qty = $(this).find("#item_qty").val();

        items.push({
            'item_id': item_id,
            'item_qty' : item_qty
        });

    });

    var request = $.ajax({
        url: '/lsvc/weborder-save?XDEBUG_SESSION_START=asdf',
        method: 'POST',
        data: {
            'items' : items,
            'week_of' : currentWeekOf
        }
    });

    request.done(function(response){

        clearForm();
        loadItems(response.data.items);

    });
}

function fetchItems(storeNumber, weekOf) {

    var request = $.ajax({
        url: '/lsvc/weborder-items',
        method: 'GET',
        data: {
            'week_of' : currentWeekOf
        }
    });

    request.done(function(response){
        loadItems(response.data.items);
    })
}

function loadItems(items)
{
    for (i=0; i<items.length; i++){
        var rowNum = i + 1;
        addRow(rowNum, items[i].item_id, items[i].item_qty, items[i].updated_at);
    }
    addEmptyRow();
}

function clearForm(){

    $("#web-order-items").empty();

}

function addEmptyRow()
{
    rowNum = $(".web-order-row").length + 1;
    addRow(rowNum, '', '');
}

function addRow(rowNum, item_id, item_qty, saved_at) {

    var html = [];

    var locked = false;

    if (item_id !== '' && item_qty !== ''){
        locked = true;
    }

    var disabled = '';

    if (locked) {
        disabled = 'disabled';
    }

    html.push("<div class='form-inline web-order-row'>");

        html.push("<span class='row-number'>"+rowNum+".&nbsp;</span>");

        html.push("<div class='form-group'>");
            html.push("<label for='exampleInputName2'>Item ID:</label>&nbsp;");
            html.push("<input "+disabled+" type='text' class='form-control' id='item_id' placeholder='ex. 41943' value='"+item_id+"'>&nbsp;");
        html.push("</div>");

        html.push("&nbsp;&nbsp;<div class='form-group'>");
            html.push("<label for='exampleInputEmail2'>QTY (cases):</label>&nbsp;");
            html.push("<input "+disabled+" type='text' class='form-control' id='item_qty' placeholder='ex. 3' value='"+item_qty+"'>&nbsp;");
        html.push("</div>");


    if (locked){

        html.push("<button type='button' class='weborder-edit btn btn-default'>");

        html.push("<span class='glyphicon glyphicon-pencil' aria-hidden='true'></span> Edit");
        html.push("</button>&nbsp;");
        html.push("<button type='button' class='weborder-delete btn btn-default'>");

        html.push("<span class='glyphicon glyphicon-trash' aria-hidden='true'></span> Delete");
        html.push("</button>");
        html.push("&nbsp;<small><em>Saved "+saved_at+"</em></small>");

    }

    html.push("</div>");

    $("#web-order-items").append(html.join(""));

}