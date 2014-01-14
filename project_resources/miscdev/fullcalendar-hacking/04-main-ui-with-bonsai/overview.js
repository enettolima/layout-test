var serviceURL = "http://cdev.newpassport.com/miscdev/fullcalendar-hacking/01-block-scheduling/service/index.php";

$(document).ready(function(){

    $('.day-button').click(function(){
        var dayOffset = $(this).attr('data-day-number');
        var weekOf = $("#rangeSelector").val();
        window.location.href = 'day.php?weekOf='+weekOf+'&dayOffset='+dayOffset;
    });

    $('#rangeSelector').change(function(){

        var selectedRange = $(this).val(); 

        var dayNames = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

        var selectedRangeParts = selectedRange.split('-'); 
        var y = parseInt(selectedRangeParts[0], 10); 
        var m = parseInt(selectedRangeParts[1], 10); 
        var d = parseInt(selectedRangeParts[2], 10); 

        for (var i=0; i<7; i++) {
            var thisDateObj = new Date(y, m-1, d+i);
            var thisDate = thisDateObj.getDate();
            var thisMonth = thisDateObj.getMonth() + 1;

            $('.day-button').eq(i).html(dayNames[thisDateObj.getDay()] + ' ' + thisMonth + '/' + thisDate);
        }

        var movie = document.getElementById('schedule-graphic-graph');

        var sunday = [];
        sunday.push( { 'eid': '301LB', 'inouts': [ {'in':'09:00', 'out':'10:00'}, {'in':'13:00', 'out':'22:00'} ] });
        sunday.push( { 'eid': '301SR', 'inouts': [ {'in':'09:00', 'out':'10:30'}, {'in':'13:00', 'out':'20:15'} ] }); 

        var monday = [];
        monday.push( { 'eid': '301SM', 'inouts': [ {'in':'11:00', 'out':'14:00'}, {'in':'15:00', 'out':'19:00'} ] });
        monday.push( { 'eid': '301SM', 'inouts': [ {'in':'11:00', 'out':'14:00'}, {'in':'15:00', 'out':'19:00'} ] });
        monday.push( { 'eid': '301LB', 'inouts': [ {'in':'08:00', 'out':'12:00'}, {'in':'12:30', 'out':'17:00'} ] });
        monday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });

        var tuesday = [];
        tuesday.push( { 'eid': '301SM', 'inouts': [ {'in':'11:00', 'out':'14:00'}, {'in':'15:00', 'out':'19:00'} ] });
        tuesday.push( { 'eid': '301SM', 'inouts': [ {'in':'11:00', 'out':'14:00'}, {'in':'15:00', 'out':'19:00'} ] });
        tuesday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });

        var wednesday = [];
        wednesday.push( { 'eid': '301SM', 'inouts': [ {'in':'11:00', 'out':'14:00'}, {'in':'15:00', 'out':'19:00'} ] });
        wednesday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });
        wednesday.push( { 'eid': '301SM', 'inouts': [ {'in':'11:00', 'out':'14:00'}, {'in':'15:00', 'out':'19:00'} ] });
        wednesday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });
        wednesday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });

        var thursday = [];
        thursday.push( { 'eid': '301SM', 'inouts': [ {'in':'11:00', 'out':'14:00'}, {'in':'15:00', 'out':'19:00'} ] });
        thursday.push( { 'eid': '301SM', 'inouts': [ {'in':'11:00', 'out':'14:00'}, {'in':'15:00', 'out':'19:00'} ] });
        thursday.push( { 'eid': '301LB', 'inouts': [ {'in':'08:00', 'out':'12:30'}, {'in':'13:00', 'out':'22:00'} ] });
        thursday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });

        var friday = [];
        friday.push( { 'eid': '301LB', 'inouts': [ {'in':'08:00', 'out':'12:30'}, {'in':'13:00', 'out':'22:00'} ] });
        friday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });
        friday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });
        friday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });
        friday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });

        var saturday = [];
        saturday.push( { 'eid': '301SM', 'inouts': [ {'in':'11:00', 'out':'14:00'}, {'in':'15:00', 'out':'19:00'} ] });
        saturday.push( { 'eid': '301LB', 'inouts': [ {'in':'08:00', 'out':'12:30'}, {'in':'13:00', 'out':'22:00'} ] });
        saturday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });
        saturday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });
        saturday.push( { 'eid': '301SR', 'inouts': [ {'in':'07:30', 'out':'11:00'}, {'in':'12:00', 'out':'18:00'} ] });

        var days = [sunday, monday, tuesday, wednesday, thursday, friday, saturday]; 

        var newDays = [
            [
                {
                    'eid' : '301LB', 
                    'inouts': [
                        {'in':'09:00', 'out':'10:00'}
                    ]
                }
            ]
        ];

        var request = $.ajax({
            url: serviceURL + "/storeWeekSchedule/301/" + selectedRange,
            type: "GET"
        });

        request.done(function(msg) {
            console.log(msg);

            bonsai.run(movie, {
                url: 'overview-bonsai-movie.js',
                width: 912,
                height: 768,
                daysData: msg
            });
        });

    });

});
