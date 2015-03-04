var empMasterDatabase = employeesFromService; // from employees.js
var currentEmployees  = Array;
var currentStore      = parseInt($("#current-store").html());
var opHours = null;

$(document).bind("ajaxSend", function(){
    $("#page-cover").css("opacity",0.15).fadeIn(100);
}).bind("ajaxComplete", function(){
    $("#page-cover").hide();
});

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
            $("#staff-picker-modal").modal('hide');
            addEmployeeToSchedule({
                "id" : $(this).attr("data-emp-id"),
                "name" : $(this).attr("data-emp-name")
            });

            // Hide this just in case it was showing, since it would now
            // be unavailable
            $("#copy-schedule-button").hide();

        }
    });
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
        // console.log(textStatus);
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

    $("#scheduler-emp-summary").empty().html("<tr><td><em>Loading</em>&nbsp;<img src='/images/ajax-loader-arrows.gif'></td></tr>");

    var summaryData = [];

    // If people are on the schedule...
    if (weekSchedule.meta.sequence && weekSchedule.meta.sequence.length) {

        // For each employee
        for (var empKey in weekSchedule.meta.sequence) {

            var empID =  weekSchedule.meta.sequence[empKey];
            // console.log("----------------- on " + empID + " -----------------------");

            var empSummaryData = {};

            empSummaryData.empID = empID;
            empSummaryData.schedDays        = [];
            empSummaryData.unscheduledSales = 0.00;
            empSummaryData.totalHours       = 0.00;
            empSummaryData.totalScheduledSales   = 0.00;
            empSummaryData.totalActualSales      = 0.00;

            for (var dayKey in weekSchedule.schedule) {

                // console.log("----------------- on day " + dayKey + " -----------------------");

                dayKey = parseInt(dayKey);

                //var daySummary = weekSchedule.meta.days[dayKey];
                var daySummary = {};
                daySummary.Ymd            = weekSchedule.meta.days[dayKey].Ymd;
                daySummary.md             = weekSchedule.meta.days[dayKey].md;
                daySummary.dayName        = weekSchedule.meta.days[dayKey].dayName;
                daySummary.hours          = 0;
                daySummary.scheduledSales = 0;

                // console.log('--------------------------');
                // console.log(actualsData.summaries.byEmp[empID]);
                /*

                var actualsForDay = actualsData.summaries.byEmp[empID].dates[daySummary.Ymd];
               */

                // console.log("typeof actualsData.summaries.byEmp[empID]:");
                // console.log(typeof actualsData.summaries.byEmp[empID]);
                // console.log(actualsData.summaries.byEmp[empID]);
                if (typeof actualsData.summaries.byEmp[empID] === "undefined" || typeof actualsData.summaries.byEmp[empID].dates[daySummary.Ymd] === "undefined") {
                    // console.log("Setting daySummary.actualsales to 0");
                    daySummary.actualSales    = 0;
                } else {
                    // console.log("Setting daySummary.actualsales to " + actualsData.summaries.byEmp[empID].dates[daySummary.Ymd]);
                    // console.log("this day:" + daySummary.Ymd);
                    daySummary.actualSales = actualsData.summaries.byEmp[empID].dates[daySummary.Ymd];
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

                // console.log("Total Hours: " + daySummary.hours); 

                if (allEmpInOuts.length) {
                    // console.log("got all the emp inouts for this day, do the budget...");

                    var goals = [];

                    // Only try to gather goals if we have targets
                    if (Object.keys(targetsData).length) {
                        for (var hour in targetsData[dayKey+1].hours) {
                            goals.push({
                                "goal" : targetsData[dayKey+1].hours[hour].budget,
                                "hour" : hour
                            });
                        }
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

                // console.log("daySummary:");
                // console.log(daySummary);

                empSummaryData.schedDays.push(daySummary);
            }

            summaryData.push(empSummaryData);
        }
    }


    // Get the emps that are on the schedule and have data, then 
    // go back and add non-scheduled emps. Don't show emps that have no inouts?



    var summaryHTML = [];

    if (summaryData.length) {
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
    } else {
        summaryHTML.push(["<tr><td><em>No staffmembers have been added to this schedule.</em></td></tr>"]);
    }

    $("#scheduler-emp-summary").empty().html(summaryHTML.join(""));
}

function summaryByDayReport(weekSchedule, targetsData, actualsData) {


    // Populate the Day Summary Data
    var weekSummaryData = [];

        for (var day=0; day <weekSchedule.meta.days.length; day++) {

            var dayObj = weekSchedule.meta.days[day];

            var daySummaryData = {};

            if (Object.keys(targetsData).length === 0 || typeof targetsData[day+1] === "undefined") {
                daySummaryData.target = 0;
            } else {
                daySummaryData.target = parseFloat(targetsData[day+1].target).toFixed(2);
            }
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

                    if (typeof weekSchedule.schedule[day] !== "undefined" && weekSchedule.schedule[day].length) {
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

                if (Object.keys(targetsData).length !== 0) {
                    for (var hour in targetsData[day+1].hours) {
                        goals.push({
                            "goal" : targetsData[day+1].hours[hour].budget,
                            "hour" : hour
                        });
                    }
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

                    var empActual = 0.00;

                    if (typeof actualsData.summaries.byDate[daySummaryData.dateFull] !== "undefined") {
                        if (typeof actualsData.summaries.byDate[daySummaryData.dateFull].emps[budgetedEmp] !== "undefined") {
                            empActual = actualsData.summaries.byDate[daySummaryData.dateFull].emps[budgetedEmp];
                        }
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

                if (typeof actualsData.summaries.byDate[daySummaryData.dateFull] !== "undefined") {
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

            }

            weekSummaryData.push(daySummaryData);
        }

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
                        empNameShortened = empNameArray[0] + " " + empNameArray[1].substr(0,1).toUpperCase() + ".";
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

    $("#scheduler-day-summary").empty().html(daySumHTML.join(""));

    var empWeekSummaryData = [];
}

function summaryOpOverviewReport(weekSchedule, targetsData, actualsData, opHoursData){

    var html = [];

    if (typeof opHoursData != 'undefined') {

        var hoursScheduled = 0;
        var managerHoursScheduled = 0;

        if (typeof weekSchedule.summary.empHoursByEmp !== "undefined") {
            var empSumm = weekSchedule.summary.empHoursByEmp;

            for (emp=0; emp<Object.keys(empSumm).length; emp++) {
                var thisEmp = Object.keys(empSumm)[emp];
                if (getEmpIsManager(thisEmp, empMasterDatabase)) {
                    managerHoursScheduled = managerHoursScheduled + empSumm[thisEmp].total;
                } else {
                    hoursScheduled = hoursScheduled + empSumm[thisEmp].total;
                }
            }
        }

        var availClass = '';

        var diff = opHoursData.TotWk_BudPayHrs - hoursScheduled;

        if (diff < 0) {
            availClass = 'text-danger bold';
        }

        html.push("<tr><td>Hours Budget:</td><td class='text-right'>" + parseNum(opHoursData.TotWk_BudPayHrs).parsed + "</td></tr>");
        html.push("<tr><td>Hours Scheduled:</td><td class='text-right'>" + parseNum(hoursScheduled).parsed + "</td></tr>");
        html.push("<tr><td><strong>Available Hours:</strong></td><td class='text-right'><span class='"+availClass+"'>" + parseNum(diff).parsed + "</span></td></tr>");
        html.push("<tr><td>Manager Hours Scheduled:</td><td class='text-right'>" + parseNum(managerHoursScheduled).parsed + "</td></tr>");
        html.push("<tr><td>Week Budget:</td><td class='text-right'>" + parseCurrency(opHoursData.TotWk_BdAmt).parsed + "</td></tr>");
        html.push("<tr><td>Overridden Days:</td><td class='text-right'>" + parseInt(opHoursData.DaysOvrd) + "</td></tr>");
    } else {
        html.push("<tr><td><em>Overview not available.</em></td></tr>");
    }

    $("#opoverview").empty().html(html.join(""));

}

function loadSchedule(strDate) {


    $("#empList").html("<li><img src=\"/images/ajax-loader-arrows.gif\"></li>");

    $("#scheduler-day-summary").empty().html("<tr><td><em>Loading</em>&nbsp;<img src='/images/ajax-loader-arrows.gif'></td></tr>");

    $("#scheduler-emp-summary").empty().html("<tr><td><em>Loading</em>&nbsp;<img src='/images/ajax-loader-arrows.gif'></td></tr>");

    // Look to see if we've changed date; if so need to change it in
    // the PHP session
    if (strDate != $("#schedulerCurrentWeekOf").val()) {
        $.ajax({
            url: '/lsvc/scheduler-set-current-week-of/' + strDate,
            type: "GET",
            async: false
        });

        $("#view-quickview-button").attr('href', '/scheduler/quickview/' + currentStore + '/' + strDate);

        $("#schedulerCurrentWeekOf").val(strDate);
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

        if ((typeof weekSchedule.meta.sequence == 'undefined') || weekSchedule.meta.sequence.length === 0) {
            $("#copy-schedule-button").show();
        } else {
            $("#copy-schedule-button").hide();
        }

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

        var operationalHours = $.ajax({
            url: "/lsvc/scheduler-operational-hours/"+currentStore+"/"+strDate,
            type: "GET"
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

                    if (typeof result[0] !== "undefined") {
                        if (typeof userCanManage !== "undefined" && userCanManage) {
                            $("#empList").append($("<li></li>").html(result[0].fullName + " <a data-user-name=\""+result[0].fullName +"\" data-user-id=\""+userId+"\" href=\"#\" class=\"small staff-remove\"><span class=\"glyphicon glyphicon-remove\"></span></a>"));
                        } else {
                            $("#empList").append($("<li></li>").html(result[0].fullName));
                        }
                    } else {
                        if (typeof userCanManage !== "undefined" && userCanManage) {
                            $("#empList").append($("<li></li>").html("DELETED EMP "+userId+" <a data-user-name=\"DELETED EMP\" data-user-id=\""+userId+"\" href=\"#\" class=\"small staff-remove\"><span class=\"glyphicon glyphicon-remove\"></span></a>"));
                        } else {
                            $("#empList").append($("<li></li>").html("DELETED EMP"));
                        }
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

                operationalHours.done(function(operationalHours){

                    if ((typeof operationalHours !== "undefined") && (typeof operationalHours.data !== "undefined")) {

                        var opHoursData = operationalHours.data[0];

                        if (parseInt(opHoursData.TotWk_OpeHrs) > 0) {

                            opHours = parseInt(opHoursData.TotWk_OpeHrs);
                        }

                        summaryOpOverviewReport(weekSchedule, targetsData, actualsData, opHoursData);
                    }

                    $(".overview-reports-warning").remove();

                    summaryByDayReport(weekSchedule, targetsData, actualsData);

                    summaryByEmpReport(weekSchedule, targetsData, actualsData);


                    if (Object.keys(targetsData).length === 0) {
                        $(".overview-reports-section").before("<p class='overview-reports-warning'><br /><em class='bg-danger'>Note: Sales targets are not yet available for this week so they are not reflected below.</em></p>");
                    }

                });
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

    $('#schedule-to-copy').change(function(){

        var strDate = $(this).val();

        var scheduleInfoRequest = $.ajax({
            url: "/lsvc/scheduler-store-week-schedule/"+currentStore+"/" + strDate,
            type: "GET"
        });

        scheduleInfoRequest.done(function(data) {

            var html = [];

            html.push("<table class='table table-striped' id='emp-hours-by-emp'>");

                html.push("<tr>");
                    html.push("<th>Day</th>");
                    html.push("<th>Emps</th>");
                    html.push("<th>Tot Hours</th>");
                html.push("</tr>");

                for (var i=0; i<7; i++) {
                    var dateString = Object.keys(data.summary.hoursByDate)[i];

                    var empsToday = '';

                    for (var x=0; x<data.schedule[i].length; x++) {

                        empsToday += data.schedule[i][x].eid;

                        if (x != data.schedule[i].length - 1) {
                            empsToday += ', ';
                        }
                    }

                    var totalHours = data.summary.hoursByDate[dateString];

                    html.push("<tr>");
                        html.push("<td>"+dateString+"</td>");
                        html.push("<td>"+empsToday+"</td>");
                        html.push("<td>"+totalHours+"</td>");
                    html.push("</tr>");
                }

            html.push("</table>");

            html.push("<a href='' class='btn btn-primary' id='do-copy-schedule'>Import This Schedule</a>");

            $("#copy-schedule-overview").html(html.join(""));

        });

    });

    $(document).on("click", "#do-copy-schedule", function(e){

        e.preventDefault();

        var 
            currentWeekOf = $("#schedulerCurrentWeekOf").val(),
            sourceWeekOf = $("#schedule-to-copy").val();

        var copyRequest = $.ajax({
            url: "/lsvc/scheduler-copy-schedule/"+currentStore + "/" + sourceWeekOf + "/" + currentWeekOf,
            type: "POST"
        });

        copyRequest.done(function(msg) {

            $("#copy-schedule-modal").modal('hide');

            loadSchedule(currentWeekOf);

        });

    });


});

$('#share-quickview-modal').on('show.bs.modal', function (e) {
    $("#share-quickview-link").val(null);
    $("#share-quickview-email").hide();
    $("#share-quickview-note").hide().val(null);
});

$(document).on("click", "#share-quickview-send-email", function(e){

    var recipients = [];
    var weekOf = $("#schedulerCurrentWeekOf").val();


    $("#share-quickview-emails li").each(function(idx, li){
        recipients.push($(this).children('input').val());
    });

    var emailReq = $.ajax({
        url: "/lsvc/scheduler-email-quickview",
        type: "POST",
        data: {
            'recipients':recipients,
            'weekOf':weekOf,
            'currentStore':currentStore,
            'link':$("#share-quickview-link").val(),
            'note':$("#share-quickview-note").val()
        }
    });

    emailReq.done(function(){
        alert("Your Email has been sent!");
        $("#share-quickview-modal").modal('hide');
    });

});

$(document).on("click", ".share-quickview-email-add", function(e){
    $("#share-quickview-emails").append("<li><input class='additional-address' type='text' placeholder='Enter Email Address'> <button class='btn btn-primary btn-xs share-quickview-email-add-confirm'>Save Recipient</button></li>");
});

$(document).on("click", ".share-quickview-email-add-confirm", function(e){
    var newEmail = $(this).parent().children('.additional-address').val();
        
    if (true /* TODO: Test email */) { 
        $(this).parent().html("<input type='checkbox' checked name='prefilled[]' value='"+newEmail+"'> "+newEmail);
    }
});

// 'Click Here to Generate Link for Sharing'
$(document).on("click", "#share-quickview-generate", function(e){

    e.preventDefault();

    var 
        weekOf = $("#schedulerCurrentWeekOf").val();

    $("#share-quickview-link-help").hide();
    $("#share-quickview-link").val('Generating link...');
    $("#share-quickview-link").attr('disabled', true);
    $("#share-quickview-email").hide();

    var request = $.ajax({
        url: "/lsvc/scheduler-quickview-share/"+currentStore+"/" + weekOf,
        type: "POST"
    });

    request.done(function(msg) {
        if (msg.token) {
            var sharelink = window.location.origin + '/scheduler/quickview/' + currentStore + '/' + weekOf + '?token=' + msg.token;
            $("#share-quickview-link").val(sharelink);
            $("#share-quickview-link").attr('disabled', false);
            $("#share-quickview-link-help").fadeIn();
            $("#share-quickview-note").fadeIn();

            var employeeRequest = $.ajax({
                url: "/lsvc/scheduler-employee-info/"+currentStore+"/"+weekOf,
                type: "GET"
            });

            employeeRequest.done(function(employeeRequestResponse){
                // console.log(Object.keys(employeeRequestResponse.users));


                for (var empInfo=0; empInfo<Object.keys(employeeRequestResponse.users).length; empInfo++) {
                    var empInfoKey = Object.keys(employeeRequestResponse.users)[empInfo];

                    var thisEmp = employeeRequestResponse.users[empInfoKey];

                    if (thisEmp.email) {
                        $("#share-quickview-emails").append("<li><input type='checkbox' checked value='"+thisEmp.email+"'> "+thisEmp.email + " (" + empInfoKey + " &mdash; " + thisEmp.full_name + ")</li>");
                    }
                }

            });

            $("#share-quickview-email").show();
        } else {
            $("#share-quickview-link").val("Error creating token! Please contact support if this problem persists.");
        }
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

$(document).on("click", "#calc", function(){

    var totsHours = '';

    if (opHours) {

        var sales = parseInt($("#calc-amount").val());

        if (sales > 200) {

            totsHours = parseNum((sales / 190) + opHours).parsed + " Hours Needed";

        } else {

            totsHours = "Unable to calculate. Please check Amount.";
        }

    } else {

        totsHours = "Unable to calculate. Forecast data not available.";
    }

    $("#calc-results").hide().html(totsHours).fadeIn();

});
