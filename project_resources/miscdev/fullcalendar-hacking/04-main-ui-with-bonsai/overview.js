var bonsaiMovie = bonsai.run(
    document.getElementById('schedule-graphic-graph'),
    {
        url: 'overview-bonsai-movie.js',
        width: 912,
        height: 768
    }
);

var serviceURL = "http://cdev.newpassport.com/miscdev/fullcalendar-hacking/01-block-scheduling/service/index.php";

function loadSchedule(strDate) {

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

        // Refresh the employees list
        $("#empList").empty();
        currentEmployees = [];

        // Reinitialize the employees database
        if (msg.meta && msg.meta.sequence) {
            for(iEmp=0; iEmp<msg.meta.sequence.length; iEmp++) {
                // Remove employee from current 
                var result = $.grep(empMasterDatabase, function(e){ return e.userId == msg.meta.sequence[iEmp]; });
                $("#empList").append($("<li></li>").html(result[0].firstName + " " + result[0].lastName + " <a href=\"#\" class=\"user-del\">x</a>"));
                currentEmployees.push(msg.meta.sequence[iEmp]);
            }
        }

        assignEmployeesToAutoComplete(empMasterDatabase, currentEmployees);

        // Trigger the Schedule Overview
        bonsaiMovie.sendMessage('externalData', {
            nodeData: { 
                "command" : "drawSchedule",
                "schedule" : msg.schedule,
                "meta" : msg.meta,
                "strDate" : strDate
            } 
        });

    });
}

$(document).ready(function(){

    loadSchedule($('#rangeSelector').val());

    $('.day-button').click(function(){
        var dayOffset = $(this).attr('data-day-number');
        var weekOf = $("#rangeSelector").val();
        window.location.href = 'day.php?weekOf='+weekOf+'&dayOffset='+dayOffset;
    });

    $('#rangeSelector').change(function(){
        loadSchedule($(this).val());
    });

});
