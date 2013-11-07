
    console.log('loading...');

    var loadedEvents = Array();

    var loadRequest = $.ajax({
        url:  "http://cdev.test/ebt/06/service/index.php/storeDaySchedule/301/2013-11-05",
        type: "GET"
    });

    loadRequest.done(function(msg) {
        console.log(msg);
        /*
        if (msg.length) {
            for (var i=0; i<msg.length; i++) {
                console.log(msg[i].associate_id);
                $('#calendar').fullCalendar(
                    'renderEvent',
                    {
                        id: 666,
                        start: '2013-11-05T13:00:00Z'
                    },
                    true
                );
            }
        }
        */
    });

    console.log('here comes loadedEvents');
    console.log(loadedEvents);
