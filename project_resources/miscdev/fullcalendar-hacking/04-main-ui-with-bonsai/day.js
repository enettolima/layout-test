var associateInOuts = {};
var loadedEvents = Array();
var serviceURL = "http://cdev.newpassport.com/miscdev/fullcalendar-hacking/01-block-scheduling/service/index.php";

$(document).ready(function() {

    var weekOf = $('#weekOf').val();
    console.log(weekOf);
    var targetDate = $('#targetDate').val();
    console.log(targetDate);

    var onEvent = 0;

    var empDateMap = {};

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
                url: serviceURL + "/inOut/301/"+associateId+"/"+targetDate+"+"+start.getHours()+"%3A"+start.getMinutes()+"%3A00/"+targetDate+"+"+end.getHours()+"%3A"+end.getMinutes()+"%3A00",
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
                url: serviceURL + "/inOutMove/"+associateId+"/"+event.id+"/"+minuteDelta,
                type: "PUT"
            });

            request.done(function(msg) {

                if (msg.status) {
                } else {
                }

            });

        },

        eventResize: function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {

            var request = $.ajax({
                url: serviceURL + "/inOutResize/"+event.id+"/"+minuteDelta,
                type: "PUT"
            });

            request.done(function(msg) {

                if (msg.status) {
                } else {
                }

            });
        },

        eventClick: function(event, jsEvent, view) {
            if (confirm('Delete this block?')) {
                $('#calendar').fullCalendar('removeEvents', event.id);

                var request = $.ajax({
                    url: serviceURL + "/inOut/" + event.id,
                    type: "DELETE"
                });

                request.done(function(msg) {

                    if (msg.status) {
                    } else {
                    }

                });
            }
        },

        editable: true
    });

    // Disable all the columns on initial load; we will then enable them
    // As we populate them
    $(".sched-col").addClass("fc-state-off");
    //$(".sched-header").html("<a class=\"adder\" href=\"#\">+ Add</a>");
    $(".sched-header").html("<button class=\"adder\">Add User</button>");

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

    function addTheUser(){
        var nextCol  = getNextAvailableColumn();
        var userInfo = $("#user").val().split(" - ");

        //Clear the selected user field
        $("#user").val("");



        var userCode = userInfo[0];
        var userNameString = userInfo[1];

        //Remove the user from the list of employees
        empMasterDatabase = $.grep(empMasterDatabase, function(o,i){ return o.userId === userCode; }, true);
        // This should probably be moved out to a "refresh autocomplete" deal...
        empAutoComplete = assignEmployees(empMasterDatabase);
        $("#user").autocomplete({source:empAutoComplete});

        // Implement: http://stackoverflow.com/questions/10405932/jquery-ui-autocomplete-when-user-does-not-select-an-option-from-the-dropdown

        var request = $.ajax({
            url: serviceURL + "/inOutColumn/301/"+targetDate+"/"+userCode,
            type: "PUT"
        });

        request.done(function(msg) {

            console.log(msg);
            console.log(msg.status);
            console.log(parseInt(msg.status));

            if (parseInt(msg.status) === 1) {
                console.log ("Ok Good");
            }
        });

        var wholeColumn   = $(".fc-col" + nextCol);

        var colHeader = $(".sched-header[data-col-id=" + nextCol + "]");

        wholeColumn.removeClass("fc-state-off");
        colHeader.html(userCode + "<br />" + userNameString);
        colHeader.attr("data-emp-id", userCode);
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

    var loadFromDB = $.ajax({
        url:  serviceURL + "/storeDaySchedule/301/"+targetDate,
        type: "GET"
    });
 
    loadFromDB.done(function(msg) {

        console.log(msg);

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
});
