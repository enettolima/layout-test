var empMasterDatabase = employeesFromService; // from employees.js
var associateInOuts = {};
var loadedEvents = Array();
var dayTargetData;
var scheduleHourLookup = null;

$(document).ready(function() {

    var targetDate = $('#targetDate').val();

    var currentStore = parseInt($("#current-store").html());

    var weekOf = $('#weekOf').val();

    var dayOffset = parseInt($('#dayOffset').val());

    var loadFromDB = $.ajax({
        url:  "/lsvc/scheduler-store-day-schedule/"+currentStore+"/"+targetDate,
        type: "GET"
    });

    var onEvent = 0;

    var empDateMap = {};
 
    loadFromDB.done(function(msg) {

        var getTargets = $.ajax({
            url: '/lsvc/scheduler-targets/'+currentStore+'/'+weekOf,
            type: 'GET'
        });

        getTargets.done(function(data){
            dayTargetData = data[dayOffset+1];
            updateSummaries(scheduleHourLookup);
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

        if (msg.scheduleHourLookup) {
            scheduleHourLookup = msg.scheduleHourLookup;
        } else {
            console.log(msg);
        }
    });


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
        selectable: true,
        selectHelper: true,
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
                    updateSummaries(msg.scheduleHourLookup);

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
                updateSummaries(msg.scheduleHourLookup);
            });

        },

        eventResize: function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {

            var request = $.ajax({
                url: "/lsvc/scheduler-in-out-resize/"+event.id+"/"+minuteDelta+"/"+currentStore+"/"+targetDate,
                type: "PUT"
            });

            request.done(function(msg) {
                updateSummaries(msg.scheduleHourLookup);
            });
        },

        eventClick: function(event, jsEvent, view) {
            if (confirm('Delete this block?')) {
                $('#calendar').fullCalendar('removeEvents', event.id);

                var request = $.ajax({
                    url: "/lsvc/scheduler-in-out/" + event.id+"/"+currentStore+"/"+targetDate,
                    type: "DELETE"
                });

                request.done(function(msg) {
                    updateSummaries(msg.scheduleHourLookup);
                });
            }
        },

        editable: true
    });

    // Disable all the columns on initial load; we will then enable them
    // As we populate them
    $(".sched-col").addClass("fc-state-off");
    //$(".sched-header").html("<a class=\"adder\" href=\"#\">+ Add</a>");
    // $(".sched-header").html("<button class=\"adder\">Add User</button>");


});

function updateSummaries(scheduleRef)
{

    $("#day-target").html("$" + parseFloat(dayTargetData.target).toFixed(2)); 

    $("#day-hours").html(dayTargetData.open + " - " + dayTargetData.close );

    $("#day-hours-detail tbody").empty();

    var openHour = parseInt(dayTargetData.open);
    var closeHour= parseInt(dayTargetData.close);
    
    for(var h=openHour; h<closeHour; h++) {
        for (var i=1; i<3; i++) {

            if (!dayTargetData.hours[h]) {
                dayTargetData.hours[h] = {"budget" : "0.00"};
            }

            var halfHourBudget = parseFloat(dayTargetData.hours[h].budget / 2).toFixed(2);

            var timeKey = (h < 10 ? '0' : '') + h + ':' + (i == 1 ? '00' : '30'); 

            var staffCount = parseInt(scheduleRef[timeKey]);

            var distributedGoal; 
            var extraClasses = '';

            if (halfHourBudget > 0) {
                if (staffCount === 0) {
                    distributedGoal = "NEED STAFF!";
                    extraClasses += "danger ";
                } else {
                    distributedGoal = "$" + (halfHourBudget / staffCount).toFixed(2);
                }
            } else {
                extraClasses += "info ";
                distributedGoal = "<em>No Goals Specified</em>";
            }


            var row = "";
            row += '<tr class="'+extraClasses+'">';
            row += '    <td class="text-center">'+timeKey+'</td>';
            row += '    <td class="text-right">$'+halfHourBudget+'</td>';
            row += '    <td class="text-center">'+staffCount+'</td>';
            row += '    <td class="text-right">'+distributedGoal+'</td>';

            row += '</tr>';
            $("#day-hours-detail").append(row);
        }
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
        return results[0].firstName + " " + results[0].lastName;
    } else {
        return false;
    }
}
