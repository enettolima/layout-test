var empMasterDatabase = employeesFromService; // from employees.js
var associateInOuts = {};
var loadedEvents = Array();
var dayTargetData;
var currentStore = null;
var targetDate = null;
var weekOf = null;
var dayOffset = null;


var inOuts = [];
var goals = [];

function timeToMin(dateString) {

    var returnval = false;

    var re = /^\d{4}-\d{2}-\d{2}\s(\d{2}):(\d{2}):(\d{2})$/;

    var matches = re.exec(dateString);

    if (matches) {
        returnval = (parseInt(matches[1]) * 60) + parseInt(matches[2]);
    } 

    return returnval;
}

$(document).ready(function() {

    targetDate = $('#targetDate').val();

    currentStore = parseInt($("#current-store").html());

    weekOf = $('#weekOf').val();

    dayOffset = parseInt($('#dayOffset').val());

    var url  = "/lsvc/scheduler-store-day-schedule/"+currentStore+"/"+targetDate;

    var loadFromDB = $.ajax({
        url:  url,
        type: "GET"
    });

    var onEvent = 0;

    var empDateMap = {};
 
    loadFromDB.done(function(msg) {

        if (msg.schedule) {
            inOuts = msg.schedule;
        }

        var getTargets = $.ajax({
            url: '/lsvc/scheduler-targets/'+currentStore+'/'+weekOf,
            type: 'GET'
        });

        getTargets.done(function(data){

            // TODO: Can I remove this from the global scope?
            dayTargetData = data[dayOffset+1];

            for (var key in data[dayOffset+1].hours) {
                goals.push({"hour" : key, "goal" : data[dayOffset+1].hours[key].budget});
            }

            newUpdateSummaries();
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
                $(".sched-header").eq(i).attr("data-emp-id", msg.meta.sequence[i]).html(msg.meta.sequence[i] + "<br />" + getEmpNameFromCode(msg.meta.sequence[i]) );
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
                    newUpdateSummaries();

                } else {
                    calendar.fullCalendar('unselect');
                }
            });

            onEvent++;
        },
        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
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
                newUpdateSummaries();
            });
        },

        eventResize: function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {

            var request = $.ajax({
                url: "/lsvc/scheduler-in-out-resize/"+event.id+"/"+minuteDelta+"/"+currentStore+"/"+targetDate,
                type: "PUT"
            });

            request.done(function(msg) {
                inOuts = msg.schedule;
                newUpdateSummaries();
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

function newUpdateSummaries()
{
    $("#day-target").html("$" + parseFloat(dayTargetData.target).toFixed(2)); 

    $("#day-hours").html(dayTargetData.open + " - " + dayTargetData.close );

    $("#new-day-hours-detail tbody").empty();

    var openHour = parseInt(dayTargetData.open);

    var closeHour= parseInt(dayTargetData.close);

    var mins = [];

    // Create a lookup of the empMins  
    for (var d=0; d<inOuts.length; d++) {

        var start = timeToMin(inOuts[d].date_in);

        var end = timeToMin(inOuts[d].date_out);

        for (var onMin = start; onMin < end; onMin++) {

            if (typeof mins[onMin] === "undefined") {
                mins[onMin] = 0;
            }

            mins[onMin]++;
        }

    }

    for (var g=0; g<goals.length; g++) {

        // Figure out the range for this hour... 

        var empMin = 0;

        var minsFrom = (goals[g].hour * 60);

        var minsTo = minsFrom + 59;

        for (atMin = minsFrom; atMin <= minsTo; atMin++) {
            if (typeof mins[atMin] !== "undefined") {
                empMin = empMin + mins[atMin];
            }
        }

        var budget = goals[g].goal / empMin;

        /*(
        console.log({
            "hour": goals[g].hour, 
            "goal" : goals[g].goal,
            "minsFrom" : minsFrom,
            "minsTo" : minsTo,
            "empMin" : empMin,
            "budget" : budget
        });
           */



        var extraClasses = '';

        var budgetOutput = '';

        if (budget === Infinity) {
            extraClasses += "danger ";
            budgetOutput = "NEED STAFF!";
        } else {
            budgetOutput = "$" + budget.toFixed(2);
        }

        var row = "";
        row += '<tr class="'+extraClasses+'">';
        row += '    <td>'+goals[g].hour+'</td>';
        row += '    <td align="right">$'+parseFloat(goals[g].goal).toFixed(2)+'</td>';
        row += '    <td class="text-center">'+empMin+'</td>';
        row += '    <td align="right">'+budgetOutput+'</td>';
        row += '</tr>';

        $("#new-day-hours-detail tbody").append(row);
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

        newUpdateSummaries();

    });
});
