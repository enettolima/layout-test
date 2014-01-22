function assignEmployeesToAutoComplete(empMasterDatabase, currentEmployees){

    console.log('assignEmployeesToAutoComplete');
    console.log(currentEmployees);

    var returnVal = Array();

    $.each(empMasterDatabase, function(key, value) {
        if (currentEmployees.indexOf(value.userId) < 1) {
            returnVal.push(value.userId + " - " + value.firstName + " " + value.lastName);
        } else {
            console.log("Not assigning " + value.userId +"|"+ value.firstName +"|");
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

    console.log("addTheUser fired for " + userInfo);

    var targetDate = $("#rangeSelector").val();
    console.log(1);

    var userCode = userInfo[0];
    console.log(2);

    var userNameString = userInfo[1];
    console.log(3);

    // Add the user to current list of employees
    currentEmployees.push(userCode);
    console.log(4);

    // This should probably be moved out to a "refresh autocomplete" deal...
    empAutoComplete = assignEmployeesToAutoComplete(empMasterDatabase, currentEmployees);
    console.log(5);

    $("#user").autocomplete({source:empAutoComplete});
    console.log(6);

    // Implement: http://stackoverflow.com/questions/10405932/jquery-ui-autocomplete-when-user-does-not-select-an-option-from-the-dropdown

    var request = $.ajax({
        url: serviceURL + "/inOutColumn/301/"+targetDate+"/"+userCode,
        type: "PUT"
    })

    .done(function(msg) {
    console.log(8);
        console.log(msg);
        // console.log(msg.status);
        // console.log(parseInt(msg.status));

        if (parseInt(msg.status) === 1) {
            console.log ("Ok Good");
            $("#empList").append($("<li></li>").html(userNameString + " <a href=\"#\" class=\"user-del\">x</a>"));
        }
    })

    .fail(function(jaXHR, textStatus){
        console.log(textStatus);
    });

    request.always(function(){
        console.log('always do this');
    });

    /*
    var wholeColumn   = $(".fc-col" + nextCol);

    var colHeader = $(".sched-header[data-col-id=" + nextCol + "]");

    wholeColumn.removeClass("fc-state-off");
    colHeader.html(userCode + "<br />" + userNameString);
    colHeader.attr("data-emp-id", userCode);
    */
}
