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

        var view = $('#calendar').fullCalendar('getView');

        if (msg.meta && msg.meta.sequence.length) {
            for (var i=0; i<msg.meta.sequence.length; i++) {

                var normalizedDate = new Date (view.start.getFullYear(), view.start.getMonth(), view.start.getDate() + i); 
                var month = normalizedDate.getMonth() + 1;
                var date = ('0' + normalizedDate.getDate()).slice(-2);

                empDateMap[msg.meta.sequence[i]] = { "calDateIndex" : normalizedDate.getFullYear() + "-" + month + "-" + date};

                // Turn on the column
                $(".fc-col" + i).removeClass("fc-state-off");

                // Label the column
                $(".sched-header").eq(i).attr("data-emp-id", msg.meta.sequence[i]).html(msg.meta.sequence[i] + "<br />" + getEmpNameFromCode(msg.meta.sequence[i], empMasterDatabase) );
            }

        }

        if (msg.schedule.length) {

            for (var b=0; b<msg.schedule.length; b++) {

                var schedObj = msg.schedule[b];

                var column = empDateMap[schedObj.associate_id].calDateIndex;

                 $('#calendar').fullCalendar('renderEvent', {
                     id: schedObj.id, 
                     allDay: false, 
                     start: column + " " + schedObj.date_in.split(" ")[1], 
                     end: column + " " + schedObj.date_out.split(" ")[1]
                 }, true);
            }
        }

    });


    var editAccess = false;

    if (typeof userCanManage !== 'undefined' && userCanManage) {
        editAccess = true;
    }

    var calendar = $('#calendar').fullCalendar({
        // We don't really care what time 
        // the calendar thinks it is because in the context 
        // of our app we're working with one day.
        //
        // However we need to map blocks to the columns
        // created by the fullCalendar library we are
        // exploiting and use those dates as an "index"
        //
        // So to help debug, let's set the date here to 1/1/2000
        // so that we're always working with the same "days"
        //
        // This also conveniently circumvents highlighting today's date
        // unless this code is transported back in time.

        month:1, 
        date:1,
        year:2000,

        defaultView: 'agendaWeek',
        header: {
            left: '',
            center: '',
            right: ''
        },
        selectable: editAccess,
        selectHelper: editAccess,
        select: function(start, end, allDay, jsEvent, view) {

            /* Ugly day-border issue hack #1: set "midnight" to 11:59:59 */
            if ((end.getHours() === 0) && (end.getMinutes() === 0) && (end.getSeconds() === 0)) {
                end.setSeconds(end.getSeconds() - 1);
            }

            var normalizedDate = new Date (start.getFullYear(), start.getMonth(), start.getDate()); 
            var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
            var column = Math.round(Math.abs((normalizedDate.getTime() - view.start.getTime())/(oneDay)));
            var associateId = $(".sched-header[data-col-id="+column+"]").attr('data-emp-id');

            var request = $.ajax({
                url: "/lsvc/scheduler-in-out/"+currentStore+"/"+associateId+"/"+targetDate+"+"+start.getHours()+"%3A"+start.getMinutes()+"%3A00/"+targetDate+"+"+end.getHours()+"%3A"+end.getMinutes()+"%3A00"+"/"+targetDate,
                type: "POST"
            });

            request.done(function(msg) {
                if (msg.id) {
                    calendar.fullCalendar(
                        'renderEvent', 
                        {
                            //title: 'foo',
                            id: msg.id,
                            start: start,
                            end: end,
                            allDay: allDay
                        },
                        true
                    );

                    inOuts = msg.schedule;
                    updateSummaries();

                } else {
                    calendar.fullCalendar('unselect');
                }
            });

            onEvent++;
        },
        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {

            /* Ugly day-border issue hack #2: subtract 1 min from delta on a "midnight" end  */
            if ((event.end.getHours() === 0) && (event.end.getMinutes() === 0) && (event.end.getSeconds() === 0)) {
                minuteDelta = minuteDelta - 1;
            }


            var normalizedDate = new Date (event.start.getFullYear(), event.start.getMonth(), event.start.getDate()); 
            var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
            var column = Math.round(Math.abs((normalizedDate.getTime() - view.start.getTime())/(oneDay)));
            var associateId = $(".sched-header[data-col-id="+column+"]").attr('data-emp-id');


            var request = $.ajax({
                url: "/lsvc/scheduler-in-out-move/"+associateId+"/"+event.id+"/"+minuteDelta+"/"+targetDate+"/"+currentStore,
                type: "PUT"
            });

            request.done(function(msg) {
                inOuts = msg.schedule;
                updateSummaries();
            
            });
        },

        eventResize: function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {

            /* Ugly day-border issue hack #3: subtract 1 min from delta on a "midnight" end  */
            /* This is extra-janky because the UI lets you select past 12:00 */
            if ((event.end.getHours() === 0) && (event.end.getMinutes() === 0) && (event.end.getSeconds() === 0)) {
                minuteDelta = minuteDelta - 1;
            }

            var request = $.ajax({
                url: "/lsvc/scheduler-in-out-resize/"+event.id+"/"+minuteDelta+"/"+currentStore+"/"+targetDate,
                type: "PUT"
            });

            request.done(function(msg) {
                inOuts = msg.schedule;
                updateSummaries();
            });
        },

        eventClick: function(event, jsEvent, view) {

            if (typeof userCanManage !== 'undefined' && userCanManage) {

                $("#block-remove-modal-content").html("<p>To delete this block, click <strong>Confirm Deletion</strong>.</p>"); 
                $("#block-remove-modal-confirm").attr("data-event-id", event.id);
                $("#block-remove-modal").modal('show');
            }
        },

        editable: editAccess
    });

    // Disable all the columns on initial load; we will then enable them
    // As we populate them
    $(".sched-col").addClass("fc-state-off");
    //$(".sched-header").html("<a class=\"adder\" href=\"#\">+ Add</a>");
    // $(".sched-header").html("<button class=\"adder\">Add User</button>");


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

function getNextAvailableColumn(){

    var columns = $(".sched-header");

    var lastSet;

    $.each(columns, function(key, value){

        var attr = $(value).attr("data-emp-id");

        if (typeof attr !== 'undefined' && attr !== false) {

            lastSet = parseInt($(value).attr("data-col-id"));
        }

    });

    var returnval = null;

    if (typeof(lastSet) === 'undefined') {
        returnval = 0;
    }else if (typeof(lastSet) === 'number' && lastSet !== 9) {
        returnval = lastSet + 1;
    } else {
        returnval = false;
    }

    return returnval;
}

/*
function getEmpNameFromCode(strCode){
    var results = $.grep(empMasterDatabase, function(e){ return e.userId === strCode; });

    if (results.length === 0) {
        return false;
    } else if (results.length === 1) {
        return results[0].fullName;
    } else {
        return false;
    }
}
*/

$(document).on("click", "#block-remove-modal-confirm", function(){

    var eventId = $(this).attr('data-event-id');

    $("#block-remove-modal").modal('hide');

    $('#calendar').fullCalendar('removeEvents', eventId);

    var request = $.ajax({
        url: "/lsvc/scheduler-in-out/" + eventId + "/" + currentStore + "/" + targetDate,
        type: "DELETE"
    });

    request.done(function(msg) {

        inOuts = msg.schedule;

        updateSummaries();
    });
});
