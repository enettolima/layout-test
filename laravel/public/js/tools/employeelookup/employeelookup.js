function doCheck(){

    resetResults();

    $("#emp-num").prop('disabled', true);

    var empNum = $("#emp-num").val();

    empNum = empNum.trim();

    var pattern = /^\d+$/;

    var responseDetails = {'success' : false, 'html' : ''};

    if (pattern.test(empNum)) {

        var request = $.ajax({
            url: "/lsvc/tools-employee-lookup/"+empNum,
            type: "GET"
        });

        request.done(function(response){


            if (typeof response.errors !== "undefined") {
                if (typeof response.errors[0].title !== "undefined") {
                    responseDetails.html += response.errors[0].title;
                } else {
                    responseDetails.html += "Search Error";
                }

            } else if (typeof response.data !== "undefined") {

                if (response.data.active) {
                    responseDetails.success = true;
                    responseDetails.activeText = 'ACTIVE';
                } else {
                    responseDetails.activeText = 'NOT ACTIVE';
                }

                responseDetails.html += "<h4> Employee "+response.data.empl_no1+" is "+responseDetails.activeText + "</h4>"; 

                responseDetails.html += "<ul class='list-unstyled'>"; 

                responseDetails.html += "<li><strong>Name:</strong> " + response.data.rpro_full_name + "</li>";
                responseDetails.html += "<li><strong>Position:</strong> " + response.data.description + "</li>";

                responseDetails.html += "</ul>"; 

                console.log(response.data);
                console.log(responseDetails);
            }

            showResults(responseDetails);

            $("#emp-num").prop('disabled', false);

        });

    } else {
        responseDetails.html = "Invalid input. ADP number is all numeric, eg. 123456789";
        showResults(responseDetails);
    }

}

function resetResults()
{
    $("#results").addClass("hidden").removeClass("bg-success").removeClass("bg-danger");
    $("#searching").removeClass("hidden");
}

function showResults(responseDetails)
{
    $("#searching").addClass("hidden");

    $("#emp-num").prop('disabled', false);

    $("#results").html(responseDetails.html);
    if (responseDetails.success) {
        $("#results").addClass("bg-success");
    } else {
        $("#results").addClass("bg-danger");
    }

    $("#results").removeClass("hidden");

    $("#emp-num").focus();
    $("#emp-num").select();
}

$(document).ready(function(){
    $("#emp-num").focus();

    $(window).keydown(function(event){
        if(event.keyCode == 13) {
            doCheck();
        }
    });

});

$("#do-check").on("click", function(e){
    doCheck();
});
