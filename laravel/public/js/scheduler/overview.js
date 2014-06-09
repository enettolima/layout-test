var empMasterDatabase = employeesFromService; // from employees.js
var currentEmployees  = Array;
var currentStore      = parseInt($("#current-store").html());

function populateEmployeeSelector(empMasterDatabase, currentEmployees) {

    for (var i=0; i<empMasterDatabase.length; i++) {
        var emp = empMasterDatabase[i];
        var addClass = null;
        if (currentEmployees.indexOf(emp.userId) > -1) {
            addClass = 'text-muted';
        }

        $("#staff-picker").append('<tr class="staffmember-row '+addClass+'" data-emp-id="'+emp.userId+'" data-emp-name="'+emp.fullName+'"><td>'+emp.userId+'</td><td>'+emp.fullName+'</td></tr>');
    }

    $("#staff-picker tr.staffmember-row").on("click", function(){
        if (currentEmployees.indexOf($(this).attr("data-emp-id")) > -1){
            // Already added; just do nothing TODO: give some sort of feedback?
        }else{
            $(this).addClass('text-muted');
            $("#staffPickerModal").modal('hide');
            addEmployeeToSchedule({
                "id" : $(this).attr("data-emp-id"),
                "name" : $(this).attr("data-emp-name")
            });
        }
    });
}

function addEmployeeToSchedule(employeeObj)
{

    var targetDate = $("#rangeSelector").val();

    if (currentEmployees.length == 0) {
        $("#empList").empty();
    }

    // Add the user to current list of employees
    currentEmployees.push(employeeObj.id);

    var request = $.ajax({
        url: "/lsvc/scheduler-in-out-column/"+currentStore+"/"+targetDate+"/"+employeeObj.id,
        type: "PUT"
    })

    .done(function(msg) {

        if (parseInt(msg.status) === 1) {
            $("#empList").append($("<li></li>").html(employeeObj.name + " <a data-user-name=\""+employeeObj.name +"\" data-user-id=\""+employeeObj.id+"\" href=\"#\" class=\"small staff-remove\"><span class=\"glyphicon glyphicon-remove\"></span></a>"));
        }
    })

    .fail(function(jaXHR, textStatus){
        console.log(textStatus);
    });
}

var bonsaiMovie = bonsai.run(
    document.getElementById('schedule-graphic-graph'),
    {
        url: '/js/scheduler/overview-bonsai-movie.js?ts=' + Date.now(),
        width: 912,
        height: 544
    }
);

function loadSchedule(strDate) {

    $("#empList").html("<li><img src=\"/images/ajax-loader-arrows.gif\"></li>");

    // Look to see if we've changed date; if so need to change it in
    // the PHP session
    if (strDate != $("#currentWeekOf").val()) {
        $.ajax({
            url: '/lsvc/scheduler-set-current-week-of/' + strDate,
            type: "GET"
        });
    }

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
        url: "/lsvc/scheduler-store-week-schedule/"+currentStore+"/" + strDate,
        type: "GET"
    });

    request.done(function(msg) {

        // Refresh the employees list
        $("#empList").empty();
        currentEmployees = [];
        $("#staff-picker tbody").empty();
        // Populate Staff listing on the page and currentEmployees
        if (msg.meta && msg.meta.sequence) {
            if (msg.meta.sequence.length > 0) {
                for(iEmp=0; iEmp<msg.meta.sequence.length; iEmp++) {
                    var userId = msg.meta.sequence[iEmp];
                    var result = $.grep(empMasterDatabase, function(e){ return e.userId == userId; });
                    if (typeof userCanManage != 'undefined' && userCanManage) {
                        $("#empList").append($("<li></li>").html(result[0].fullName + " <a data-user-name=\""+result[0].fullName +"\" data-user-id=\""+userId+"\" href=\"#\" class=\"small staff-remove\"><span class=\"glyphicon glyphicon-remove\"></span></a>"));
                    } else {
                        $("#empList").append($("<li></li>").html(result[0].fullName));
                    }
                    currentEmployees.push(userId);
                }
            } 
        } 

        if (currentEmployees.length < 1) {
            $("#empList").append($("<li><em>No staffmembers have been added to this schedule.</em></li>"));
        }

        populateEmployeeSelector(empMasterDatabase, currentEmployees);

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

        $("#day-summary").append("<tr><td colspan=\"2\" class=\"heady\">Sunday ["+msg.summary.hoursByDayNum[0]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[0]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[0][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\" class=\"heady\">Monday ["+msg.summary.hoursByDayNum[1]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[1]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[1][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\" class=\"heady\">Tuesday ["+msg.summary.hoursByDayNum[2]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[2]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[2][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\" class=\"heady\">Wednesday ["+msg.summary.hoursByDayNum[3]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[3]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[3][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\" class=\"heady\">Thursday ["+msg.summary.hoursByDayNum[4]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[4]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[4][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\" class=\"heady\">Friday ["+msg.summary.hoursByDayNum[5]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[5]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[5][emp]+"</td></tr>");
        }

        $("#day-summary").append("<tr><td colspan=\"2\" class=\"heady\">Saturday ["+msg.summary.hoursByDayNum[6]+" Hours]</td></tr>");
        for (var emp in msg.summary.empHoursByDayNum[6]) {
            $("#day-summary").append("<tr><td>"+emp+":</td><td>"+msg.summary.empHoursByDayNum[6][emp]+"</td></tr>");
        }

        // Populate the Week Summary
        $("#week-summary").empty();

        for (var emp in msg.summary.empHoursByEmp) {

            var totalHours = msg.summary.empHoursByEmp[emp].total;
            var days = msg.summary.empHoursByEmp[emp].days;

            $("#week-summary").append("<tr><td class=\"heady\" colspan=\"2\">"+emp+" ["+totalHours+" Hours]</td></tr>");

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

    if ($("#currentWeekOf").val()) {
        loadSchedule($('#currentWeekOf').val());
    } else {
        loadSchedule($('#rangeSelector').val());
    }

    $('.day-button').click(function(){
        var dayOffset = $(this).attr('data-day-number');
        var weekOf = $("#rangeSelector").val();
        window.location.href = '/scheduler/day-planner?weekOf='+weekOf+'&dayOffset='+dayOffset;
    });

    $('#rangeSelector').change(function(){
        loadSchedule($(this).val());
    });

});

$(document).on("click", ".staff-remove", function(){

    var empId = $(this).attr('data-user-id');

    var empFullName = $(this).attr('data-user-name');

    $("#staff-remove-modal-content").html("<p>Are you sure you want to remove <strong>"+empId+" "+empFullName+"</strong> from this week's schedule?");
    $("#staff-remove-modal-confirm").attr("data-emp-id", empId);
    $("#staff-remove-modal").modal('show');
});

$(document).on("click", "#staff-remove-modal-confirm", function(){

    $("#staff-remove-modal").modal('hide');

    var userId = $(this).attr("data-emp-id");

    var weekOf = $("#rangeSelector").val();

    var request = $.ajax({
        url: "/lsvc/scheduler-remove-user/"+currentStore+"/" + userId + "/" + weekOf,
        type: "DELETE"
    });

    request.done(function(msg) {
        // We don't have to reactivate the employee in the selector because 
        // We reload
        loadSchedule(weekOf);
    });
});
