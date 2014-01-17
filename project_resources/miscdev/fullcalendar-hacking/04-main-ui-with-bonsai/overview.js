var serviceURL = "http://cdev.newpassport.com/miscdev/fullcalendar-hacking/01-block-scheduling/service/index.php";

var bonsaiMovie = bonsai.run(
    document.getElementById('schedule-graphic-graph'),
    {
        url: 'overview-bonsai-movie.js',
        width: 912,
        height: 768
    }
);

function loadWeek(strDate) {

        var dayNames = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

        var selectedRangeParts = strDate.split('-'); 
        var y = parseInt(selectedRangeParts[0], 10); 
        var m = parseInt(selectedRangeParts[1], 10); 
        var d = parseInt(selectedRangeParts[2], 10); 

        for (var i=0; i<7; i++) {
            var thisDateObj = new Date(y, m-1, d+i);
            var thisDate = thisDateObj.getDate();
            var thisMonth = thisDateObj.getMonth() + 1;

            $('.day-button').eq(i).html(dayNames[thisDateObj.getDay()] + ' ' + thisMonth + '/' + thisDate);
        }

        var request = $.ajax({
            url: serviceURL + "/storeWeekSchedule/301/" + strDate,
            type: "GET"
        });

        request.done(function(msg) {
            bonsaiMovie.sendMessage('externalData', {
                nodeData: { 
                    "command" : "drawSchedule",
                    "data" : msg
                } 
            });
        });
}

$(document).ready(function(){

    loadWeek($('#rangeSelector').val());

    $('.day-button').click(function(){
        var dayOffset = $(this).attr('data-day-number');
        var weekOf = $("#rangeSelector").val();
        window.location.href = 'day.php?weekOf='+weekOf+'&dayOffset='+dayOffset;
    });

    $('#rangeSelector').change(function(){
        loadWeek($(this).val());
    });

});
