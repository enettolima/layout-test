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
                var userId = msg.meta.sequence[iEmp];
                var result = $.grep(empMasterDatabase, function(e){ return e.userId == userId; });
                $("#empList").append($("<li></li>").html(result[0].firstName + " " + result[0].lastName + " <a data-user-id=\""+userId+"\" href=\"#\" class=\"user-del small\"><span class=\"glyphicon glyphicon-remove\"></span></a>"));
                currentEmployees.push(userId);
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

        // Populate the Day Summary

        $("#day-summary").empty();

        $("#day-summary").append("<tr><td colspan=\"2\">Sunday ["+msg.summary.hoursByDayNum[0]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[0]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[0][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\">Monday ["+msg.summary.hoursByDayNum[1]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[1]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[1][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\">Tuesday ["+msg.summary.hoursByDayNum[2]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[2]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[2][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\">Wednesday ["+msg.summary.hoursByDayNum[3]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[3]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[3][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\">Thursday ["+msg.summary.hoursByDayNum[4]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[4]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[4][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\">Friday ["+msg.summary.hoursByDayNum[5]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[5]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[5][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\">Saturday ["+msg.summary.hoursByDayNum[6]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[6]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[6][emp]+"</td></tr>");
        }

        // Populate the Week Summary
        $("#week-summary").empty();

        for (var emp in msg.summary.empHoursByEmp) {

            var totalHours = msg.summary.empHoursByEmp[emp].total;
            var days = msg.summary.empHoursByEmp[emp].days;

            $("#week-summary").append("<tr><td colspan=\"2\">"+emp+" ["+totalHours+" Hours]</td></tr>");

            if (typeof(days[0].string) === "undefined") {
                days[0].string = '<em>No hours</em>';
            }
            $("#week-summary").append("<tr><td>Sunday:</td><td>"+days[0].string+"</td></tr>");

            if (typeof(days[1].string) === "undefined") {
                days[1].string = '<em>No hours</em>';
            }
            $("#week-summary").append("<tr><td>Monday:</td><td>"+days[1].string+"</td></tr>");

            if (typeof(days[2].string) === "undefined") {
                days[2].string = '<em>No hours</em>';
            }
            $("#week-summary").append("<tr><td>Tuesday:</td><td>"+days[2].string+"</td></tr>");

            if (typeof(days[3].string) === "undefined") {
                days[3].string = '<em>No hours</em>';
            }
            $("#week-summary").append("<tr><td>Wednesday:</td><td>"+days[3].string+"</td></tr>");

            if (typeof(days[4].string) === "undefined") {
                days[4].string = '<em>No hours</em>';
            }
            $("#week-summary").append("<tr><td>Thursday:</td><td>"+days[4].string+"</td></tr>");
            
            if (typeof(days[5].string) === "undefined") {
                days[5].string = '<em>No hours</em>';
            }
            $("#week-summary").append("<tr><td>Friday:</td><td>"+days[5].string+"</td></tr>");

            if (typeof(days[6].string) === "undefined") {
                days[6].string = '<em>No hours</em>';
            }
            $("#week-summary").append("<tr><td>Saturday:</td><td>"+days[6].string+"</td></tr>");

        }
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

$(document).on("click", ".user-del", function(){
    if (confirm("Are you sure?")) {

        var userId = $(this).attr("data-user-id");
        var weekOf = $("#rangeSelector").val();

        var request = $.ajax({
            url: serviceURL + "/removeUserFromSchedule/301/" + userId + "/" + weekOf,
            type: "DELETE"
        });

        request.done(function(msg) {
            loadSchedule(weekOf);
        });
    }
});
