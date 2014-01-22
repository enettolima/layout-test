function assignEmployeesToAutoComplete(empMasterDatabase, currentEmployees){

    var returnVal = Array();

    $.each(empMasterDatabase, function(key, value) {
        if (currentEmployees.indexOf(value.userId) < 1) {
            returnVal.push(value.userId + " - " + value.firstName + " " + value.lastName);
        }
    });

    return returnVal;
}

var currentEmployees = [];
var empAutoComplete = assignEmployeesToAutoComplete(empMasterDatabase, currentEmployees);

$(".adder").on("click", function(){
    $( "#dialog" ).dialog({
        modal:true,
        buttons: {
            "Add User" : function() {
                $(this).dialog("close");
                addTheUser();
            },
            "Cancel" : function(){
                $(this).dialog("close");
            }
        }
    });
});

$("#dialog").keypress(function(e) {
    if (e.keyCode == $.ui.keyCode.ENTER) {
        $(this).dialog("close");
        addTheUser();
    }
});

$("#user").autocomplete({source:empAutoComplete, autoFocus:true});

function addTheUser(){

    //var nextCol  = getNextAvailableColumn();
    var userInfo = $("#user").val().split(" - ");

    var targetDate = $("#rangeSelector").val();

    var userCode = userInfo[0];

    var userNameString = userInfo[1];

    // Add the user to current list of employees
    currentEmployees.push(userCode);

    // This should probably be moved out to a "refresh autocomplete" deal...
    empAutoComplete = assignEmployeesToAutoComplete(empMasterDatabase, currentEmployees);

    $("#user").autocomplete({source:empAutoComplete});

    // Implement: http://stackoverflow.com/questions/10405932/jquery-ui-autocomplete-when-user-does-not-select-an-option-from-the-dropdown

    var request = $.ajax({
        url: serviceURL + "/inOutColumn/301/"+targetDate+"/"+userCode,
        type: "PUT"
    })

    .done(function(msg) {

        if (parseInt(msg.status) === 1) {
            $("#empList").append($("<li></li>").html(userNameString + " <a href=\"#\" class=\"user-del\">x</a>"));
        }
    })

    .fail(function(jaXHR, textStatus){
        console.log(textStatus);
    });

    /*
    var wholeColumn   = $(".fc-col" + nextCol);

    var colHeader = $(".sched-header[data-col-id=" + nextCol + "]");

    wholeColumn.removeClass("fc-state-off");
    colHeader.html(userCode + "<br />" + userNameString);
    colHeader.attr("data-emp-id", userCode);
    */
}
