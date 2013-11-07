var associateInOuts = {};
    var loadedEvents = Array();

$(document).ready(function() {

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
            var associateId = $(".sched-col[data-col-id="+column+"]").attr('data-emp-id');

            var request = $.ajax({
                url: "http://cdev.test/ebt/06/service/index.php/inOut/301/"+associateId+"/2013-11-05+"+start.getHours()+"%3A"+start.getMinutes()+"%3A00/2013-11-05+"+end.getHours()+"%3A"+end.getMinutes()+"%3A00",
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
                }
            });

            onEvent++;
        },
        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
            var normalizedDate = new Date (event.start.getFullYear(), event.start.getMonth(), event.start.getDate()); 
            var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
            var column = Math.round(Math.abs((normalizedDate.getTime() - view.start.getTime())/(oneDay)));
            var associateId = $(".sched-col[data-col-id="+column+"]").attr('data-emp-id');

            var request = $.ajax({
                url: "http://cdev.test/ebt/06/service/index.php/inOutMove/"+associateId+"/"+event.id+"/"+minuteDelta,
                type: "PUT"
            });

            request.done(function(msg) {

                if (msg.status) {
                    console.log('InOut Moved');
                } else {
                    console.log('InOut Not Moved');
                }

            });

        },

        eventResize: function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {

            var request = $.ajax({
                url: "http://cdev.test/ebt/06/service/index.php/inOutResize/"+event.id+"/"+minuteDelta,
                type: "PUT"
            });

            request.done(function(msg) {

                if (msg.status) {
                    console.log('InOut Resized');
                } else {
                    console.log('InOut Not Resized');
                }

            });
        },

        eventClick: function(event, jsEvent, view) {
            if (confirm('Delete this block?')) {
                $('#calendar').fullCalendar('removeEvents', event.id);

                var request = $.ajax({
                    url: "http://cdev.test/ebt/06/service/index.php/inOut/" + event.id,
                    type: "DELETE"
                });

                request.done(function(msg) {

                    if (msg.status) {
                        console.log('InOut Deleted');
                    } else {
                        console.log('InOut Not Deleted');
                    }

                });
            }
        },

        editable: true
    });

    var loadFromDB = $.ajax({
        url:  "http://cdev.test/ebt/06/service/index.php/storeDaySchedule/301/2013-11-05",
        type: "GET"
    });
 
    loadFromDB.done(function(msg) {

        var view = $('#calendar').fullCalendar('getView');
        console.log(view.start);
        console.log(view.end);

        if (msg.meta.sequence.length) {
            for (var i=0; i<msg.meta.sequence.length; i++) {
                $(".emp-col").eq(i).attr("data-emp-id", msg.meta.sequence[i]).html(msg.meta.sequence[i]);

                var normalizedDate = new Date (view.start.getFullYear(), view.start.getMonth(), view.start.getDate() + i); 
                var month = normalizedDate.getMonth() + 1;
                var date = ('0' + normalizedDate.getDate()).slice(-2);

                empDateMap[msg.meta.sequence[i]] = { "calDateIndex" : normalizedDate.getFullYear() + "-" + month + "-" + date};
            }
        }

        if (msg.schedule.length) {

            for (var b=0; b<msg.schedule.length; b++) {

                var schedObj = msg.schedule[b];

                console.log(schedObj);

                console.log(schedObj.associate_id);

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
