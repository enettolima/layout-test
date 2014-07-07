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

/*
 * take two datetime strings: 2013-01-01 13:45:00
 * and, disregarding the date itself, return the elapsed
 * hours
 */
function hoursFromInOut(date_in, date_out)
{
    var inParts = date_in.split(" ")[1].split(":");
    var inMins = (parseInt(inParts[0]) * 60) + parseInt(inParts[1]); 
    var outParts = date_out.split(" ")[1].split(":");
    var outMins = (parseInt(outParts[0]) * 60) + parseInt(outParts[1]); 

    return (outMins - inMins) / 60;
}

/*
 * take 2014-01-01 11:00:00 and return "11:00am"
 */
function inOutLabel(dateString)
{
    var timeArr = dateString.split(" ")[1].split(":");

    var hour = timeArr[0];
    var mins = timeArr[1];

    var hourLabel = '';
    var ampm = '';

    if (hour > 12) {
        hour = (hour - 12);
        ampm = 'pm';
    } else if (hour === 0) {
        hour = '12';
        ampm = 'am';
    } else if (hour == 12) {
        hour = '12';
        ampm = 'pm';
    } else {
        ampm = 'am';
    }

    return hour + ":" + mins + ampm;
}

function addEmployeeToSchedule(employeeObj)
{

    var targetDate = $("#rangeSelector").val();

    if (currentEmployees.length === 0) {
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

function summaryByEmpReport(weekSchedule, targetsData, actualsData){

    console.log(weekSchedule);
    console.log(targetsData);
    console.log(actualsData);

    var summaryData = [];

    if (weekSchedule.meta.sequence.length) {
        for (var empKey in weekSchedule.meta.sequence) {

            var empID =  weekSchedule.meta.sequence[empKey];
            console.log("----------------- on " + empID + " -----------------------");

            var empSummaryData = {};

            empSummaryData.empID = empID;
            empSummaryData.schedDays        = [];
            empSummaryData.unscheduledSales = 0.00;
            empSummaryData.totalHours       = 0.00;
            empSummaryData.totalScheduledSales   = 0.00;
            empSummaryData.totalActualSales      = 0.00;

            for (var dayKey in weekSchedule.schedule) {

                console.log("----------------- on day " + dayKey + " -----------------------");

                dayKey = parseInt(dayKey);

                //var daySummary = weekSchedule.meta.days[dayKey];
                var daySummary = {};
                daySummary.Ymd            = weekSchedule.meta.days[dayKey].Ymd;
                daySummary.md             = weekSchedule.meta.days[dayKey].md;
                daySummary.dayName        = weekSchedule.meta.days[dayKey].dayName;
                daySummary.hours          = 0;
                daySummary.scheduledSales = 0;

                var actualsForDay = actualsData.summaries.byEmp[empID].dates[daySummary.Ymd];

                if (typeof actualsForDay !== "undefined") {
                    daySummary.actualSales = actualsForDay;
                } else {
                    daySummary.actualSales    = 0;
                }

                empSummaryData.totalActualSales += daySummary.actualSales;

                // Collect all the inOuts for this day so we can get the budget
                var allEmpInOuts = [];

                if (weekSchedule.schedule[dayKey].length) {

                    for (var schedEmpKey in weekSchedule.schedule[dayKey]) {

                        var inOuts = weekSchedule.schedule[dayKey][schedEmpKey].inouts;

                        if (inOuts.length) {
                            for (var ioKey in inOuts) {

                                var thisInOut = {
                                    "associate_id" : weekSchedule.schedule[dayKey][schedEmpKey].eid,
                                    "date_in" : "2014-01-01 " + inOuts[ioKey].in + ":00",
                                    "date_out" : "2014-01-01 " + inOuts[ioKey].out + ":00",
                                    "id" : "000",
                                    "store_id" : "000"
                                };

                                allEmpInOuts.push(thisInOut);

                                if (weekSchedule.schedule[dayKey][schedEmpKey].eid == empID) {
                                    var hoursElapsed = hoursFromInOut(thisInOut.date_in, thisInOut.date_out);
                                    daySummary.hours += hoursElapsed;
                                    empSummaryData.totalHours += hoursElapsed;
                                }
                            }
                        }
                    }
                }

                console.log("Total Hours: " + daySummary.hours); 

                if (allEmpInOuts.length) {
                    console.log("got all the emp inouts for this day, do the budget...");

                    var goals = [];

                    for (var hour in targetsData[dayKey+1].hours) {
                        goals.push({
                            "goal" : targetsData[dayKey+1].hours[hour].budget,
                            "hour" : hour
                        });
                    }

                    // Todo: Modify this so that we aren't running it over and over.
                    // We only need to compute the day budget once for each day, 
                    // here we are computing it for each day for every employee.
                    var SS = new SchedulerSummary(goals, allEmpInOuts);
                    var budgetByEmployee = SS.getBudgetByEmployee();

                    if (typeof budgetByEmployee[empID] !== "undefined") {
                        daySummary.scheduledSales = budgetByEmployee[empID];
                        empSummaryData.totalScheduledSales += budgetByEmployee[empID];
                    }
                }

                console.log("daySummary:");
                console.log(daySummary);

                empSummaryData.schedDays.push(daySummary);
            }

            summaryData.push(empSummaryData);
        }
    }

    console.log(summaryData);

    // Get the emps that are on the schedule and have data, then 
    // go back and add non-scheduled emps. Don't show emps that have no inouts?

    var summaryHTML = [];

    for (var summaryKey in summaryData) {

        var diffWeekSales = summaryData[summaryKey].totalActualSales - summaryData[summaryKey].totalScheduledSales;

        summaryHTML.push([
            "<tr class='info day-header'>",
                "<td class='info day-label' rowspan='2'>"+summaryData[summaryKey].empID+"</td>",
                "<td align='right'>Sch Sales</td>",
                "<td align='right'>Act Sales</td>",
                "<td align='right'>Diff</td>",
                "<td align='right'>Sch Hours</td>",
            "</tr>",
            "<tr class='info day-header'>",
                "<td align='right'>"+summaryData[summaryKey].totalScheduledSales.toFixed(2)+"</td>",
                "<td align='right'>"+summaryData[summaryKey].totalActualSales.toFixed(2)+"</td>",
                "<td align='right'>"+diffWeekSales.toFixed(2)+"</td>",
                "<td align='right'>"+summaryData[summaryKey].totalHours.toFixed(2)+"</td>",
            "</tr>"
        ]);

        for (var schedDayKey in summaryData[summaryKey].schedDays){

            var schedDay = summaryData[summaryKey].schedDays[schedDayKey];

            var diffDaySales = schedDay.actualSales - schedDay.scheduledSales;

            summaryHTML.push([
                "<tr>",
                    "<td align='right'>"+schedDay.dayName+" "+schedDay.md+"</td>",
                    "<td align='right'>"+schedDay.scheduledSales.toFixed(2)+"</td>",
                    "<td align='right'>"+schedDay.actualSales.toFixed(2)+"</td>",
                    "<td align='right'>"+diffDaySales.toFixed(2)+"</td>",
                    "<td align='right'>"+schedDay.hours.toFixed(2)+"</td>",
                "</tr>"
            ]);
        }
    }

    $("#scheduler-emp-summary").empty().html(summaryHTML.join(""));
}

function summaryByDayReport(weekSchedule, targetsData, actualsData) {

    // Populate the Day Summary Data
    var weekSummaryData = [];

    for (var day=0; day <weekSchedule.meta.days.length; day++) {

        var dayObj = weekSchedule.meta.days[day];

        var daySummaryData = {};

        daySummaryData.target = parseFloat(targetsData[day+1].target).toFixed(2);
        daySummaryData.dayName = dayObj.dayName;
        daySummaryData.dateLabel = dayObj.md;
        daySummaryData.dateFull = dayObj.Ymd;
        daySummaryData.empTarget = 0.00; // Initialize this
        daySummaryData.scheduledEmps = [];
        daySummaryData.totalHours = 0;

        var empInOuts = [];

        // For each Person attached to this schedule
        if (typeof weekSchedule.meta.sequence !== "undefined" && weekSchedule.meta.sequence.length) {
            for (var emp=0; emp<weekSchedule.meta.sequence.length; emp++) {

                var empID = weekSchedule.meta.sequence[emp];

                if (weekSchedule.schedule[day].length) {
                    // This day has some inouts. Iterate through them looking
                    // for our current person
                    for (var sumIO=0; sumIO<weekSchedule.schedule[day].length; sumIO++){

                        var inOutSet = weekSchedule.schedule[day][sumIO];

                        if (inOutSet.eid === empID) {
                            for (var empIO=0; empIO<inOutSet.inouts.length; empIO++) {

                                empInOuts.push({
                                    "associate_id" : empID,
                                    "date_in" : "2014-01-01 " + inOutSet.inouts[empIO].in + ":00",
                                    "date_out" : "2014-01-01 " + inOutSet.inouts[empIO].out + ":00",
                                    "id" : "000",
                                    "store_id" : "000"
                                });
                            }
                        }
                    }
                }
            }
        }

        if (empInOuts.length) {

            var goals = [];

            for (var hour in targetsData[day+1].hours) {
                goals.push({
                    "goal" : targetsData[day+1].hours[hour].budget,
                    "hour" : hour
                });
            }

            // I HAVE THE empInOuts and the goals for SS!
            var SS = new SchedulerSummary(goals, empInOuts);
            var budgetByEmployee = SS.getBudgetByEmployee();

            for (var budgetedEmp in budgetByEmployee) {

                var thisEmpTotalHours = 0;

                var thisEmpInOuts = [];

                for(var eio=0; eio<empInOuts.length; eio++) {
                    if (empInOuts[eio].associate_id === budgetedEmp) {

                        var hoursElapsed = hoursFromInOut(empInOuts[eio].date_in, empInOuts[eio].date_out);

                        daySummaryData.totalHours += parseFloat(hoursElapsed);

                        thisEmpTotalHours += hoursElapsed;

                        empInOuts[eio].hoursElapsed = hoursElapsed;

                        thisEmpInOuts.push(empInOuts[eio]);
                    }
                } 

                var empActual = actualsData.summaries.byDate[daySummaryData.dateFull].emps[budgetedEmp];

                if (typeof empActual === "undefined") {
                    empActual = 0.00;
                } else {
                    empActual = empActual.toFixed(2);
                }

                var thisEmp = {
                    "empID" : budgetedEmp, 
                    "empTarget" : budgetByEmployee[budgetedEmp],
                    "empInOuts" : thisEmpInOuts,
                    "empTotalHours" : thisEmpTotalHours,
                    "empActual" : empActual
                };

                daySummaryData.empTarget += budgetByEmployee[budgetedEmp];

                daySummaryData.scheduledEmps.push(thisEmp);
            }

            // Here I need to add employees that got time but weren't in the schedule.

            var empsFromActuals = actualsData.summaries.byDate[daySummaryData.dateFull].emps;

            for (var empFromActual in empsFromActuals) {

                if (typeof budgetByEmployee[empFromActual] === "undefined") {

                    var unscheduledEmp = {
                        "empID" : empFromActual + " (U)",
                        "empTarget" : 0,
                        "empInOuts" : [],
                        "empActual": empsFromActuals[empFromActual],
                        "empTotalHours" : undefined
                    };

                    daySummaryData.scheduledEmps.push(unscheduledEmp);

                }
            }

        }

        weekSummaryData.push(daySummaryData);
    }

    $("#scheduler-day-summary").empty();

    // Create the Day Summary HTML
    var daySumHTML = [];

    for (var d=0; d<weekSummaryData.length; d++) {

        var summaryDay = weekSummaryData[d];

        summaryDay.actual = 0;

        if (actualsData.summaries.byDate[summaryDay.dateFull]) {
            summaryDay.actual = actualsData.summaries.byDate[summaryDay.dateFull].total;
        }

        summaryDay.target    = parseFloat(summaryDay.target).toFixed(2);
        summaryDay.empTarget = parseFloat(summaryDay.empTarget).toFixed(2);
        summaryDay.diff      = parseFloat(summaryDay.actual - summaryDay.target).toFixed(2);

        if (summaryDay.diff < 0) {
            summaryDay.diff = "<span class=\"text-danger bg-danger\">" + summaryDay.diff + "</span>";
        } else if (summaryDay.diff > 0) {
            summaryDay.diff = "<span class=\"text-success\">" + summaryDay.diff + "</span>";
        }

        daySumHTML.push([
            "<tr class='info day-header'>",
            "<td align='left' rowspan='2' class='day-label'>" + summaryDay.dayName + "<br />" + summaryDay.dateLabel + "</td>",
            "<td align='right'>Goal</td>",
            "<td align='right'>Scheduled</td>",
            "<td align='right'>Actual</td>",
            "<td align='right'>Diff</td>",
            "<td align='right'>Hours</td>",
            "</tr>",
            "<tr class='info day-header'>",
            "<td align='right'>"+summaryDay.target+"</td>",
            "<td align='right'>"+summaryDay.empTarget+"</td>",
            "<td align='right'>"+summaryDay.actual.toFixed(2)+"</td>",
            "<td align='right'>"+summaryDay.diff+"</td>",
            "<td align='right'>"+parseFloat(summaryDay.totalHours).toFixed(2)+"</td>",
            "</tr>"
        ]);

        if (weekSummaryData[d].scheduledEmps.length) {

            for (var changeMeA=0; changeMeA<weekSummaryData[d].scheduledEmps.length; changeMeA++) {

                var e = weekSummaryData[d].scheduledEmps[changeMeA];

                e.empTarget = parseFloat(e.empTarget).toFixed(2);
                e.empDiff = parseFloat(e.empActual - e.empTarget).toFixed(2);

                if (e.empDiff < 0) {
                    e.empDiff = "<span class=\"text-danger bg-danger\">" + e.empDiff + "</span>";
                } else if (e.empDiff > 0) {
                    e.empDiff = "<span class=\"text-success\">" + e.empDiff + "</span>";
                }


                if (e.empTotalHours === undefined) {
                    e.empTotalHours = "N/A";
                } else {
                    e.empTotalHours = parseFloat(e.empTotalHours).toFixed(2);
                }

                var empNameString = getEmpNameFromCode(e.empID, empMasterDatabase);

                empNameArray = empNameString.split(" "); //[0] + " " + empNameString.split(" ")[1].substring(1,1) + ".";

                var empNameShortened = empNameString;

                if (empNameArray.length === 2) {
                    empNameShortened = empNameArray[0] + " " + empNameArray[1].substr(1,1).toUpperCase() + ".";
                }

                daySumHTML.push([
                    "<tr class='warning emp-header'>",
                    "<td align='left' colspan='2'>"+e.empID+" ("+empNameShortened+")</td>",
                    "<td align='right'>"+e.empTarget+"</td>",
                    "<td align='right'>"+parseFloat(e.empActual).toFixed(2)+"</td>",
                    "<td align='right'>"+e.empDiff+"</td>",
                    "<td align='right'>"+e.empTotalHours+"</td>",
                    "</tr>"
                ]);

                for (var cio=0; cio<e.empInOuts.length; cio++) {

                    var changeMeB = e.empInOuts[cio];

                    changeMeB.date_in = inOutLabel(changeMeB.date_in);
                    changeMeB.date_out = inOutLabel(changeMeB.date_out);

                    daySumHTML.push([
                        "<tr>",
                        "<td></td>",
                        "<td></td>",
                        "<td></td>",
                        "<td align='right'>"+changeMeB.date_in+"</td>",
                        "<td align='right'>"+changeMeB.date_out+"</td>",
                        "<td align='right'>"+changeMeB.hoursElapsed.toFixed(2)+"</td>",
                        "</tr>"
                    ]);

                }
            }
        }
    }

    $("#scheduler-day-summary").html(daySumHTML.join(""));

    var empWeekSummaryData = [];
}

function loadSchedule(strDate) {

    $("#empList").html("<li><img src=\"/images/ajax-loader-arrows.gif\"></li>");

    // Look to see if we've changed date; if so need to change it in
    // the PHP session
    if (strDate != $("#schedulerCurrentWeekOf").val()) {
        $.ajax({
            url: '/lsvc/scheduler-set-current-week-of/' + strDate,
            type: "GET",
            async: false
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

    var weekScheduleRequest = $.ajax({
        url: "/lsvc/scheduler-store-week-schedule/"+currentStore+"/" + strDate,
        type: "GET"
    });

    weekScheduleRequest.done(function(weekSchedule) {

        var targetsRequest = $.ajax({
            url: "/lsvc/scheduler-targets/"+currentStore+"/"+strDate,
            type: "GET",
            // async: false
        });

        var actualsRequest = $.ajax({
            url: "/lsvc/scheduler-actuals/"+currentStore+"/"+strDate,
            type: "GET",
            // async: false
        });

        // Refresh the employees list
        $("#empList").empty();
        currentEmployees = [];
        $("#staff-picker tbody").empty();
        // Populate Staff listing on the page and currentEmployees
        if (weekSchedule.meta && weekSchedule.meta.sequence) {
            if (weekSchedule.meta.sequence.length > 0) {
                for(iEmp=0; iEmp<weekSchedule.meta.sequence.length; iEmp++) {
                    var userId = weekSchedule.meta.sequence[iEmp];
                    var result = $.grep(empMasterDatabase, function(e){ return e.userId == userId; });
                    if (typeof userCanManage !== "undefined" && userCanManage) {
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
                "schedule" : weekSchedule.schedule,
                "meta" : weekSchedule.meta,
                "strDate" : strDate
            } 
        });

        targetsRequest.done(function(targetsData){

            actualsRequest.done(function(actualsData){

                summaryByDayReport(weekSchedule, targetsData, actualsData);

                summaryByEmpReport(weekSchedule, targetsData, actualsData);
            });
        });

    });

}

$(document).ready(function(){

    if ($("#schedulerCurrentWeekOf").val()) {
        loadSchedule($('#schedulerCurrentWeekOf').val());
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
