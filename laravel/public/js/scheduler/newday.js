var empMasterDatabase = employeesFromService; // from employees.js
var dayTargetData;
var currentStore = null;
var targetDate = null;
var weekOf = null;
var dayOffset = null;

var inOuts = [];
var goals = [];

$(document).bind("ajaxSend", function(){
    $("#page-cover").css("opacity",0.15).fadeIn(100);
}).bind("ajaxComplete", function(){
    $("#page-cover").hide();
});

$(document).ready(function() {

    targetDate = $('#targetDate').val();

    currentStore = parseInt($("#current-store").html());

    weekOf = $('#weekOf').val();

    dayOffset = parseInt($('#dayOffset').val());

    var url  = "/lsvc/scheduler-store-day-schedule/"+currentStore+"/"+targetDate;

    var loadFromDB = $.ajax({
        url:  url,
        type: "GET",
        global: false
    });

    var onEvent = 0;

    var empDateMap = {};
 
    loadFromDB.done(function(msg) {

        if (msg.schedule) {
            inOuts = msg.schedule;
        }

        var getTargets = $.ajax({
            url: '/lsvc/scheduler-targets/'+currentStore+'/'+weekOf,
            type: 'GET',
            global: false
        });

        getTargets.done(function(data){

            // TODO: Can I remove this from the global scope?
            dayTargetData = data[dayOffset+1];

            if (typeof dayTargetData !== "undefined") {
                for (var key in data[dayOffset+1].hours) {
                    goals.push({"hour" : key, "goal" : data[dayOffset+1].hours[key].budget});
                }
            }

            updateSummaries();

        });
    });

    var editAccess = false;

    if (typeof userCanManage !== 'undefined' && userCanManage) {
        editAccess = true;
    }

});

function milToStandard(hour)
{
    var hourLabel = '';

    if (hour > 12) {
        hourLabel = (hour - 12) + 'pm';
    } else if (hour === 0) {
        hourLabel = '12am';
    } else if (hour == 12) {
        hourLabel = '12pm';
    } else {
        hourLabel = hour + 'am';
    }

    return hourLabel;
}

function updateSummariesNotReady()
{
    $("#day-target").html("<em>Target data not available yet.</em>"); 
    $("#day-hours").html("<em>Target data not available yet.</em>"); 
    $("#new-day-hours-detail tbody").append("<tr><td colspan='99'><em>Target data not available yet.</em></td></tr>");
    $("#emp-hours-summary tbody").append("<tr><td colspan='99'><em>Target data not available yet.</em></td></tr>");
}

function updateSummaries()
{
    //if (true) {
    if (typeof dayTargetData !== "undefined") {
        $("#day-target").html("$" + parseFloat(dayTargetData.target).toFixed(2)); 

        $("#day-hours").html(milToStandard(dayTargetData.open) + " - " + milToStandard(dayTargetData.close));

        $("#new-day-hours-detail tbody").empty();

        var SS = new SchedulerSummary(goals, inOuts);

        var budgetByHour = SS.getBudgetByHour();

        var row = null;

        for (var b=0; b<budgetByHour.length; b++) {
            
            var extraClasses = '';

            var budgetOutput = '';

            if (budgetByHour[b].budget === Infinity) {
                extraClasses += "danger ";
                budgetOutput = "NEED STAFF!";
            } else {
                budgetOutput = "$" + budgetByHour[b].budget.toFixed(2);
            }

            var hourLabel = milToStandard(budgetByHour[b].hour);

            row = "";
            row += '<tr class="'+extraClasses+'">';
            // row += '    <td>'+budgetByHour[b].hour+'</td>';
            row += '    <td>'+hourLabel+'</td>';
            row += '    <td align="right">$'+parseFloat(budgetByHour[b].goal).toFixed(2)+'</td>';
            row += '    <td class="text-center">'+budgetByHour[b].empMin+'</td>';
            row += '    <td align="right">'+budgetOutput+'</td>';
            row += '</tr>';

            $("#new-day-hours-detail tbody").append(row);
        }

        $("#emp-hours-summary tbody").empty();

        var budgetByEmployee = SS.getBudgetByEmployee();

        for (var emp in budgetByEmployee) {
            row = "";
            row += "<tr>";
            row += "<td>"+emp+"</td>";
            row += "<td>"+getEmpNameFromCode(emp, empMasterDatabase)+"</td>";
            row += "<td class=\"text-right\">$"+parseFloat(budgetByEmployee[emp]).toFixed(2)+"</td>";
            row += "</tr>";

            $("#emp-hours-summary tbody").append(row);
        }
    }else{
        $("#day-target").html("<em>Target data not available yet.</em>"); 
        $("#day-hours").html("<em>Target data not available yet.</em>"); 
        $("#new-day-hours-detail tbody").append("<tr><td colspan='99'><em>Target data not available yet.</em></td></tr>");
        $("#emp-hours-summary tbody").append("<tr><td colspan='99'><em>Target data not available yet.</em></td></tr>");
    }
}

$(document).on("click", ".btn-inout-add", function(){

    var html = [];

    html.push("<tr data-event-id='new'>");
    html.push("<td style='width:7em;'><input type='text' class='input-inout input-inout-in form-control input-sm' disabled value='09:00am' data-previous-value='09:00am'></td>");
    html.push("<td style='width:.8em;'>&mdash;</td>");
    html.push("<td style='width:7em;'><input type='text' class='input-inout input-inout-out form-control input-sm' disabled value='11:00am' data-previous-value='11:00am'></td>");
    html.push("<td>");
    html.push("<div class='btn-group'>");
    html.push("<button class='btn btn-default btn-sm btn-inout-edit'>Edit</button>");
    html.push("</div>");
    html.push("<button class='btn btn-default btn-sm btn-inout-delete'>Delete</button>");
    html.push("</tr>");
    html.push("</tr>");

    $(this).closest('tr').before(html.join("\n"));
});

$(document).on("click", ".btn-inout-edit", function(){
    // Enable editing of the inout inputs
    $(this).closest('tr').find('.input-inout').prop('disabled', false);

    // Add the 'Save Changes' changes button
    $(this).closest('.btn-group').append("<button class='btn btn-default btn-sm btn-inout-save'>Save Changes</button>");

    // Add the 'Cancel' changes button
    $(this).closest('.btn-group').append("<button class='btn btn-default btn-sm btn-inout-cancel'>Cancel</button>");

    // Remove this button
    $(this).remove();
});

$(document).on("click", ".btn-inout-cancel", function(){
    var inputIn  = $(this).closest('tr').find('.input-inout-in');
    var inputOut = $(this).closest('tr').find('.input-inout-out');
    inputIn.val(inputIn.attr('data-previous-value'));
    inputOut.val(inputOut.attr('data-previous-value'));
    $(this).closest('tr').find('.input-inout').prop('disabled', true);

    // Replace the 'Edit' button
    $(this).closest('.btn-group').append("<button class='btn btn-default btn-sm btn-inout-edit'>Edit</button>");

    // Remove the 'Save Changes' button
    $(this).closest('.btn-group').find('.btn-inout-save').remove();

    // Remove the 'Cancel' button
    $(this).remove();
});

// "Delete Button" on an In/Out Row
$(document).on("click", ".btn-inout-delete", function(){

    var inoutRow = $(this).closest('tr');
    var inoutId = inoutRow.attr("data-event-id");

    $("#inout-delete-modal-content").html("<p>Are you sure you want to delete this In/Out?</p>"); 
    $("#inout-delete-modal-confirm").attr("data-event-id", inoutId);
    $("#inout-delete-modal").modal('show');
});

// "Confirm Delete" on an In/Out Row
$(document).on("click", "#inout-delete-modal-confirm", function(){

    var inoutId = $(this).attr('data-event-id');
    var inoutRow = $("tr[data-event-id='"+inoutId+"']");

    $("#inout-delete-modal").modal('hide');

    inoutRow.remove();

    console.log(inoutId);
    console.log(inoutRow);

});
