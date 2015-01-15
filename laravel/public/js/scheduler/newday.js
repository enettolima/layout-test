var empMasterDatabase = employeesFromService; // from employees.js
var dayTargetData;
var currentStore      = null;
var targetDate        = null;
var weekOf            = null;
var dayOffset         = null;

var inOuts = [];
var goals  = [];

var editAccess = false;

if (typeof userCanManage !== 'undefined' && userCanManage) {
    editAccess = true;
}

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

    var dayScheduleRequest = $.ajax({
        url: "/lsvc/scheduler-store-day-schedule/"+currentStore+"/"+targetDate,
        type: "GET"
    });

    var targetsRequest = $.ajax({
        url: '/lsvc/scheduler-targets/'+currentStore+'/'+weekOf,
        type: 'GET'
    });

    $.when(dayScheduleRequest, targetsRequest).done(function(schedRes, targetsRes){

        var sched = schedRes[0];
        //console.log(sched);

        var targets = targetsRes[0];
        //console.log(targets);

        inOuts = sched.schedule;
        buildStaff(sched);

        dayTargetData = targets[dayOffset+1];

        if (typeof dayTargetData !== "undefined") {
            for (var key in targets[dayOffset+1].hours) {
                goals.push({"hour" : key, "goal" : targets[dayOffset+1].hours[key].budget});
            }
        }

        updateSummaries();

    });

});

function buildStaff(sched)
{

    var currentEmp = null;
    var seenEmps = [];

    for (var e=0; e<sched.meta.sequence.length; e++) {

        if (currentEmp !== sched.meta.sequence[e]) {
            currentEmp = sched.meta.sequence[e];
        }

        var staffHtml = [];
        staffHtml.push("<li>");
            staffHtml.push("<a href='#"+currentEmp+"' data-toggle='tab'>");
                staffHtml.push(currentEmp+" - "+getEmpNameFromCode(currentEmp, empMasterDatabase));
            staffHtml.push("</a>");
        staffHtml.push("</li>");
        $("#staff").append(staffHtml.join(""));

        buildInOuts(currentEmp, inOuts);
    }
}

function buildInOuts(emp, inOuts)
{
    var staffInOutsTableHtml = [];

    var controlDisable = 'disabled="true"';

    if (typeof userCanManage !== 'undefined' && userCanManage) {
        controlDisable = null;
    } 

    staffInOutsTableHtml.push("<div class='tab-pane staffmember-inout-listing' id='"+emp+"'>");
    staffInOutsTableHtml.push("<strong>"+emp+" - "+getEmpNameFromCode(emp, empMasterDatabase)+"</strong>");
    staffInOutsTableHtml.push("<table class='table table-inout-listing'>");
    staffInOutsTableHtml.push("<tr>");
    staffInOutsTableHtml.push("<th>In</th>");
    staffInOutsTableHtml.push("<th></th>");
    staffInOutsTableHtml.push("<th>Out</th>");
    staffInOutsTableHtml.push("</tr>");
    staffInOutsTableHtml.push("<tr>");
    staffInOutsTableHtml.push("<td colspan='100'>");
    staffInOutsTableHtml.push("<button "+controlDisable+" class='btn btn-primary btn-sm btn-inout-add'>Add Clock In/Out</button>");
    staffInOutsTableHtml.push("</td>");
    staffInOutsTableHtml.push("</tr>");
    staffInOutsTableHtml.push("</table>");
    staffInOutsTableHtml.push("</div>");

    $("#staff-inouts").append(staffInOutsTableHtml.join(""));

    for (var io=0; io<inOuts.length; io++) {
        if (inOuts[io].associate_id === emp) {
            var inOutControlHtml = getInOutControlHtml(
                inOuts[io].id, 
                moment(inOuts[io].date_in), 
                moment(inOuts[io].date_out)
            );

            $("div#"+emp+">table tr:last").before(inOutControlHtml);
        }
    }
}

// todo: get rid of this and only use moment
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



function turnEditOff(inOutControlRow) {
    //$(this).closest('tr').find('.input-inout').prop('disabled', true);
    inOutControlRow.find('.input-inout').prop('disabled', true);

    // Replace the 'Edit' button
    inOutControlRow.find('.btn-group').append("<button class='btn btn-default btn-sm btn-inout-edit'>Edit</button>");

    // Remove the 'Save Changes' button
    inOutControlRow.find('.btn-inout-save').remove();

    // Remove the 'Cancel' button
    inOutControlRow.find('.btn-inout-cancel').remove();
}

function turnEditOn(inOutControlRow) {
    // Enable editing of the inout inputs
    inOutControlRow.find('.input-inout').prop('disabled', false);

    // Add the 'Save Changes' button
    // Add the 'Cancel'  button
    inOutControlRow.find('.btn-group')
        .append("<button class='btn btn-default btn-sm btn-inout-save'>Save Changes</button>")
        .append("<button class='btn btn-default btn-sm btn-inout-cancel'>Cancel</button>");

    // Remove 'Edit' button
    inOutControlRow.find('.btn-inout-edit').remove();
}



function getInOutControlHtml(inOutId, inDateMoment, outDateMoment) {

    inOutId = typeof inOutId !== 'undefined' ? inOutId : 'new'; 
    inDateMoment = typeof inDateMoment !== 'undefined' ? inDateMoment : null;
    outDateMoment = typeof outDateMoment !== 'undefined' ? outDateMoment : null;

    var inDateString = '';
    var outDateString = '';

    if ( moment.isMoment(inDateMoment) && moment.isMoment(outDateMoment)) {
        inDateString = inDateMoment.format("h:mma");
        outDateString = outDateMoment.format("h:mma");
    }
        
    var html = [];

    var controlDisable = 'disabled="true"';
    if (typeof userCanManage !== 'undefined' && userCanManage) {
        controlDisable = null;
    } 

    html.push("<tr class='inout-control' data-inout-id='"+inOutId+"'>");
    html.push("<td style='width:7em;'><input type='text' class='input-inout input-inout-in form-control input-sm' disabled value='"+inDateString+"' data-previous-value='"+inDateString+"'></td>");
    html.push("<td style='width:.8em;'>&mdash;</td>");
    html.push("<td style='width:7em;'><input type='text' class='input-inout input-inout-out form-control input-sm' disabled value='"+outDateString+"' data-previous-value='"+outDateString+"'></td>");
    html.push("<td>");
    html.push("<div class='btn-group'>");
    html.push("<button "+controlDisable+" class='btn btn-default btn-sm btn-inout-edit'>Edit</button>");
    html.push("</div>");
    html.push("<button "+controlDisable+" class='btn btn-default btn-sm btn-inout-delete'>Delete</button>");
    html.push("</tr>");
    html.push("</tr>");

    return (html.join("\n"));
}

/*
 * Bindings
 */

// Modal - "Confirm Delete" button clicked
$(document).on("click", "#inout-delete-modal-confirm", function(){

    var inOutId = $(this).attr('data-inout-id');
    var inOutControlRow = $("tr[data-inout-id='"+inOutId+"']");

    $("#inout-delete-modal").modal('hide');

    var request = $.ajax({
        url : "/lsvc/scheduler-in-out/"+inOutId+"/"+currentStore+"/"+targetDate,
        type : "DELETE"
    });

    request.done(function(msg){
        if (msg.status == 1) {
            inOutControlRow.remove();
            inOuts = msg.schedule;
            updateSummaries();
        } else {
            alert("Error deleting In/Out. If the problem persists please contact support.");
        }
    });

    request.error(function(msg){
        alert("Error deleting In/Out. If the problem persists please contact support.");
    });

});

// Employee InOuts Area -- 'Add' button clicked to add InOut Control
$(document).on("click", ".btn-inout-add", function(){

    var controlHtml = getInOutControlHtml();

    $(this).closest('tr').before(controlHtml);

    var editRow = $(this).closest('tr').prev();

    turnEditOn(editRow);
});

// InOut Control -- 'Delete' button clicked
$(document).on("click", ".btn-inout-delete", function(){

    var inoutRow = $(this).closest('tr');
    var inoutId = inoutRow.attr("data-inout-id");

    $("#inout-delete-modal-content").html("<p>Are you sure you want to delete this In/Out?</p>"); 
    $("#inout-delete-modal-confirm").attr("data-inout-id", inoutId);
    $("#inout-delete-modal").modal('show');
});

// InOut Control -- 'Save' button clicked
$(document).on("click", ".btn-inout-save", function(){

    var inOutControlRow = $(this).closest("tr.inout-control");

    var inOutId = inOutControlRow.attr('data-inout-id');

    var inControl = inOutControlRow.find(".input-inout-in");
    var inVal = inControl.val();

    var outControl = inOutControlRow.find(".input-inout-out");
    var outVal = outControl.val();

    if (inVal === inControl.attr('data-previous-value') && outVal === outControl.attr('data-previous-value')) {

        // There were no changes, just turn off editing
        turnEditOff(inOutControlRow);

    } else {

        // There were changes to the control, we need to save.
        // TODO: Input Validation
        var empId = $(this).closest("div.staffmember-inout-listing").attr("id");

        var inMoment  = moment(targetDate + " " + inVal, "YYYY-MM-DD h:mma"); //.format("YYYY-MM-DD HH:mm:00");
        var inString  = inMoment.format("YYYY-MM-DD HH:mm:00");
        inString = encodeURIComponent(inString);
        var inField = inMoment.format("h:mma");
        inControl.val(inField);

        var outMoment = moment(targetDate + " " + outVal, "YYYY-MM-DD h:mma"); //.format("YYYY-MM-DD HH:mm:00");
        var outString = outMoment.format("YYYY-MM-DD HH:mm:00");
        outString = encodeURIComponent(outString);
        var outField = outMoment.format("h:mma");
        outControl.val(outField);

        var url = null;
        var request = null;

        if (inOutId === 'new') {

            url = "/lsvc/scheduler-in-out/"+currentStore+"/"+empId+"/"+inString+"/"+outString+"/"+targetDate;

            request = $.ajax({
                url: url,
                type: "POST"
            });

            request.done(function(msg){
                if(msg.status) {
                    turnEditOff(inOutControlRow);
                    inOutControlRow.attr('data-inout-id', msg.id);
                    inOuts = msg.schedule;
                    updateSummaries();
                } else {
                    alert ("Error saving In/Out");
                }
            });

            request.error(function(msg){
                alert("Fatal error saving In/Out. If this problem persists please contact IT Support");
            });

        } else {

            url = "/lsvc/scheduler-in-out/"+currentStore+"/"+inOutId+"/"+inString+"/"+outString+"/"+targetDate;

            request = $.ajax({
                url: url,
                type: "PUT"
            });

            request.done(function(msg){
                if(msg.status) {
                    turnEditOff(inOutControlRow);
                    inOuts = msg.schedule;
                    updateSummaries();
                } else {
                    alert ("Error saving In/Out");
                }
            });

            request.error(function(msg){
                alert("Fatal error saving In/Out. If this problem persists please contact IT Support");
            });

        }
    }
});

// InOut Control -- 'Edit' button clicked
$(document).on("click", ".btn-inout-edit", function(){

    var inOutControlRow = $(this).closest('tr.inout-control');

    turnEditOn(inOutControlRow);

});

// InOut Control -- 'Cancel' button clicked
$(document).on("click", ".btn-inout-cancel", function(){

    var inOutControlRow = $(this).closest('tr.inout-control');

    var inOutId = inOutControlRow.attr('data-inout-id');

    if (inOutId === 'new') {
        /*
         * This was a new InOut so to 'Cancel' changes
         * we remove the new row
         */
        inOutControlRow.remove();
    } else {

        /*
         * This is an existing InOut so to 'cancel' changes
         * we revert to what was there before.
         */

        var inputIn  = inOutControlRow.find('.input-inout-in');
        var inputOut = inOutControlRow.find('.input-inout-out');

        // Replace previous values
        inputIn.val(inputIn.attr('data-previous-value'));
        inputOut.val(inputOut.attr('data-previous-value'));

        // Turn edit off
        turnEditOff(inOutControlRow);
    }
});
