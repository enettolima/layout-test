var serviceURL = "http://cdev.newpassport.com/miscdev/fullcalendar-hacking/01-block-scheduling/service/index.php";

$(document).ready(function(){

    $('.day-button').click(function(){
        var dayOffset = $(this).attr('data-day-number');
        var weekOf = $("#rangeSelector").val();
        window.location.href = 'day.php?weekOf='+weekOf+'&dayOffset='+dayOffset;
    });

    var bMovie = null;

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

        var request = $.ajax({
            url: serviceURL + "/storeWeekSchedule/301/" + selectedRange,
            type: "GET"
        });

        request.done(function(msg) {
            console.log(msg);

            if (bMovie) {
                bMovie.destroy();
            }

            bMovie = bonsai.run(movie, {
                url: 'overview-bonsai-movie.js',
                width: 912,
                height: 768,
                daysData: msg
            });
        });

    });

});
