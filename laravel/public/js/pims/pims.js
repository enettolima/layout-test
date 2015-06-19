function doSearch()
{
    resetResults();

    $("#searching").show();

    var pcn = $("#product-search-field").val();

    var url = "/lsvc/product-info/" + pcn;

    var productRequest = $.ajax({
        url: url,  
        type: "GET"
    });

    productRequest.done(function(res){
        if (res && typeof res.data !== "undefined") {
            $("#product-info-form #rp-name").val(res.data.name);
            $("#product-info-form #rp-description").val(res.data.description);

            if (typeof res.data.pi !== "undefined") {
                $("#product-info-form #pims-field1").val(res.data.pi.field1);
            }
            $("#searching").hide();
            $("#product-info-form").show();
        } else {
            $("#searching").hide();
            $("#not-found").show();
        }
    });
}

function resetResults()
{
    $("#product-info-form").hide();
    $("#not-found").hide();
    $("#product-info-form #rp-name").val(null);
    $("#product-info-form #rp-description").val(null);
}

function savePi()
{
    var pcn = $("#product-search-field").val();

    var updatePiURL = "/lsvc/product-info/" + pcn;

    var updatePiRequest = $.ajax({
        url: updatePiURL,
        type: "POST",
        data: {
            "field1" : $("#pims-field1").val()
        }
    });

    updatePiRequest.done(function(res){
        console.log(res);
        $("#save-pi-results").html("<em><strong>Product Information Updated Successfully!</strong></em>");
        $("#save-pi-results").addClass("text-success");
        $("#save-pi-results").show();
    });
}


$(document).ready(function(){

    $("#product-search-field").focus();
    $("#product-info-form").hide();

    $(window).keydown(function(event){
        if(event.keyCode == 13) {
            doSearch();
        }
    });

    $("#product-search-button").on("click", function(){ doSearch(); });

    $("#save-pi").on("click", function(){
        savePi();
    });
});
