var 
    empMasterDatabase = employeesFromService, // from employees.js
    currentEmployees  = Array,
    storeNumber       = null,
    weekOf            = null
;

function getEmpSummaryData (sched, targets, actuals)
{
    var summaryData = [];

    if (sched.meta.sequence && sched.meta.sequence.length) {
        for (var empKey in sched.meta.sequence) {

            var empID =  sched.meta.sequence[empKey];

            var empSummaryData = {};

            empSummaryData.empID = empID;
            empSummaryData.schedDays        = [];
            empSummaryData.unscheduledSales = 0.00;
            empSummaryData.totalHours       = 0.00;
            empSummaryData.totalScheduledSales   = 0.00;
            empSummaryData.totalActualSales      = 0.00;

            for (var dayKey in sched.schedule) {

                dayKey = parseInt(dayKey);

                var daySummary = {};
                daySummary.Ymd            = sched.meta.days[dayKey].Ymd;
                daySummary.md             = sched.meta.days[dayKey].md;
                daySummary.dayName        = sched.meta.days[dayKey].dayName;
                daySummary.hours          = 0;
                daySummary.scheduledSales = 0;

                if (typeof actuals.summaries.byEmp[empID] === "undefined" || typeof actuals.summaries.byEmp[empID].dates[daySummary.Ymd] === "undefined") {
                    daySummary.actualSales    = 0;
                } else {
                    daySummary.actualSales = actuals.summaries.byEmp[empID].dates[daySummary.Ymd];
                }

                empSummaryData.totalActualSales += daySummary.actualSales;

                var allEmpInOuts = [];

                if (sched.schedule[dayKey].length) {

                    for (var schedEmpKey in sched.schedule[dayKey]) {

                        var inOuts = sched.schedule[dayKey][schedEmpKey].inouts;

                        if (inOuts.length) {
                            for (var ioKey in inOuts) {

                                var thisInOut = {
                                    "associate_id" : sched.schedule[dayKey][schedEmpKey].eid,
                                    "date_in" : "2014-01-01 " + inOuts[ioKey].in + ":00",
                                    "date_out" : "2014-01-01 " + inOuts[ioKey].out + ":00",
                                    "id" : "000",
                                    "store_id" : "000"
                                };

                                allEmpInOuts.push(thisInOut);

                                if (sched.schedule[dayKey][schedEmpKey].eid == empID) {
                                    var hoursElapsed = hoursFromInOut(thisInOut.date_in, thisInOut.date_out);
                                    daySummary.hours += hoursElapsed;
                                    empSummaryData.totalHours += hoursElapsed;
                                }
                            }
                        }
                    }
                }

                if (allEmpInOuts.length) {

                    var goals = [];

                    if (Object.keys(targets).length !== 0) {
                        for (var hour in targets[dayKey+1].hours) {
                            goals.push({
                                "goal" : targets[dayKey+1].hours[hour].budget,
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


                empSummaryData.schedDays.push(daySummary);
            }

            summaryData.push(empSummaryData);
        }
    }


    // Get the emps that are on the schedule and have data, then 
    // go back and add non-scheduled emps. Don't show emps that have no inouts?

    if (summaryData.length) {
        for (var summaryKey in summaryData) {

            var diffWeekSales = summaryData[summaryKey].totalActualSales - summaryData[summaryKey].totalScheduledSales;

            for (var schedDayKey in summaryData[summaryKey].schedDays){

                var schedDay = summaryData[summaryKey].schedDays[schedDayKey];

                var diffDaySales = schedDay.actualSales - schedDay.scheduledSales;

            }
        }
    } else {
        //summaryHTML.push(["<tr><td><em>No staffmembers have been added to this schedule.</em></td></tr>"]);
    }

    return summaryData; 
}

function getDaySummaryData (sched, targets, actuals)
{
    var daySummaries = [];

    for (var day=0; day<sched.meta.days.length; day++) {
        var dayObj = sched.meta.days[day];
        var daySummaryData = {};

        daySummaryData.target        = 0;

        if (Object.keys(targets).length !== 0 && typeof targets[day+1] !== "undefined") {
            daySummaryData.target        = parseFloat(targets[day+1].target).toFixed(2);
        }

        daySummaryData.dayName       = dayObj.dayName;
        daySummaryData.dateLabel     = dayObj.md;
        daySummaryData.dateFull      = dayObj.Ymd;
        daySummaryData.empTarget     = 0.00; // Initialize this
        daySummaryData.scheduledEmps = [];
        daySummaryData.totalHours    = 0;

        var empInOuts = [];

        // For each Person attached to this schedule
        if (typeof sched.meta.sequence !== "undefined" && sched.meta.sequence.length) {
            for (var emp=0; emp<sched.meta.sequence.length; emp++) {

                var empID = sched.meta.sequence[emp];

                if (sched.schedule[day].length) {
                    // This day has some inouts. Iterate through them looking
                    // for our current person
                    for (var sumIO=0; sumIO<sched.schedule[day].length; sumIO++){

                        var inOutSet = sched.schedule[day][sumIO];

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

            if (Object.keys(targets).length !== 0) {
                for (var hour in targets[day+1].hours) {
                    goals.push({
                        "goal" : targets[day+1].hours[hour].budget,
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

                if (typeof actuals.summaries.byDate[daySummaryData.dateFull] !== "undefined") {
                    if (typeof actuals.summaries.byDate[daySummaryData.dateFull].emps[budgetedEmp] !== "undefined") {
                        empActual = actuals.summaries.byDate[daySummaryData.dateFull].emps[budgetedEmp];
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

            if (typeof actuals.summaries.byDate[daySummaryData.dateFull] !== "undefined") {
                var empsFromActuals = actuals.summaries.byDate[daySummaryData.dateFull].emps;

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
        daySummaries.push(daySummaryData);
    }

    return daySummaries;

}

$(document).ready(function(){

    storeNumber = $("#store-number").val();
    weekOf      = $("#week-of").val();

    var weekScheduleRequest = $.ajax({
        url: "/lsvc/scheduler-store-week-schedule/"+storeNumber+"/" + weekOf,
        type: "GET"
    });

    var targetsRequest = $.ajax({
        url: "/lsvc/scheduler-targets/"+storeNumber+"/"+weekOf,
        type: "GET",
    });

    var actualsRequest = $.ajax({
        url: "/lsvc/scheduler-actuals/"+storeNumber+"/"+weekOf,
        type: "GET",
    });


    $.when(weekScheduleRequest, targetsRequest, actualsRequest).done(function(schedRes, targetsRes, actualsRes){

        var headerData = [];

        var sched = schedRes[0];
        // console.log(sched);

        var targets = targetsRes[0];
        // console.log(targets);


        if (Object.keys(targets).length === 0) {
            $(".quickview-header").after("<em class='bg-danger'>Note: Sales targets are not yet available for this week so they are not reflected below.</em>");
        }

        var actuals = actualsRes[0];
        // console.log(actuals);

        var daySummaries = getDaySummaryData(sched, targets, actuals);
        // console.log(daySummaries);

        var empSummaries = getEmpSummaryData(sched, targets, actuals);

        var html = [];

        /*-------------------------------------------------------------------*/
        html.push(
            "<tr>",
                "<th></th>",
                "<th class='text-center'>Sun</th>",
                "<th class='text-center'>Mon</th>",
                "<th class='text-center'>Tue</th>",
                "<th class='text-center'>Wed</th>",
                "<th class='text-center'>Thu</th>",
                "<th class='text-center'>Fri</th>",
                "<th class='text-center'>Sat</th>",
                "<th class='text-center'>Total</th>",
            "</tr>"
        );

        /*-------------------------------------------------------------------*/
        html.push("<tr>");

            html.push("<td><strong>Date</strong></td>");

            for (var i=0; i<sched.meta.days.length; i++) {
                html.push("<td class='text-center'>" + sched.meta.days[i].md + "</td>");
                headerData.push({'column' : sched.meta.days[i].Ymd});
            }

            html.push("<td></td>");

        html.push("</tr>");

        headerData.push({'column' : 'total'});


        /*-------------------------------------------------------------------*/
        var targetTotal = 0;
        html.push("<tr class='more-detail'>");
            html.push("<td><strong>Sales Goal</strong></td>");

            for (var salesgoal=1; salesgoal<=7; salesgoal++) {

                var salesgoalAct = 0;

                if (Object.keys(targets).length !== 0 && typeof targets[salesgoal] !== "undefined") {
                    salesgoalAct = parseFloat(targets[salesgoal].target);
                }

                html.push("<td class='text-center'>$" + salesgoalAct.toFixed(2) + "</td>");

                targetTotal = targetTotal + salesgoalAct;

                headerData[salesgoal-1].goal = salesgoalAct;
            }

            headerData[7].goal = targetTotal;
            html.push("<td class='text-center'>$"+targetTotal.toFixed(2)+"</td>");
        html.push("</tr>");

        /*-------------------------------------------------------------------*/
        var hoursTotal = 0;
        html.push("<tr class='more-detail'>");
            html.push("<td><strong>Projected Working Hours</strong></td>");
            for (var projhours=0; projhours<daySummaries.length; projhours++) {
                html.push("<td class='text-center'>"+daySummaries[projhours].totalHours.toFixed(2)+"</td>");
                hoursTotal = hoursTotal + parseFloat(daySummaries[projhours].totalHours);
                daySummaries[projhours].goalPPPH = 999;
                headerData[projhours].hours = daySummaries[projhours].totalHours;
            }
            html.push("<td class='text-center'>"+hoursTotal.toFixed(2)+"</td>");
            headerData[7].hours = hoursTotal;
        html.push("</tr>");

        /*-------------------------------------------------------------------*/
        var actualsTotal = 0;
        html.push("<tr class='more-detail'>");
            html.push("<td><strong>Actual Sales</strong></td>");
            for (var dayActual=0; dayActual<sched.meta.days.length; dayActual++) {
                var dayActualYmd = sched.meta.days[dayActual].Ymd;
                var dayActualObj = actuals.summaries.byDate[dayActualYmd];
                var dayActualStr = '-';
                headerData[dayActual].actuals = 0;

                if (typeof dayActualObj !== "undefined") {
                    var dayActualAmt = parseFloat(dayActualObj.total).toFixed(2);
                    dayActualStr = "$"+dayActualAmt;
                    actualsTotal = actualsTotal + parseFloat(dayActualAmt);
                    headerData[dayActual].actuals = dayActualAmt;
                }

                html.push("<td class='text-center'>"+dayActualStr+"</td>");
            }

            if (actualsTotal === 0) {
                html.push("<td class='text-center'>-</td>");
            } else {
                html.push("<td class='text-center'>$"+actualsTotal.toFixed(2)+"</td>");
            }
            headerData[7].actuals = actualsTotal;
        html.push("</tr>");

        /*-------------------------------------------------------------------*/
        html.push("<tr class='more-detail'>");
            html.push("<td><strong>PCT Over/Under</strong></td>");
            for (var pct=0; pct <= 7; pct++) {


                if (headerData[pct].actuals === 0) {
                    html.push("<td class='text-center'>n/a</td>");
                } else {
                    //var pctString = ((parseFloat(headerData[pct].actuals) / parseFloat(headerData[pct].goal)) * 100).toFixed();
                    var pctActuals = parseFloat(headerData[pct].actuals);
                    var pctGoal = parseFloat(headerData[pct].goal);
                    var pctString = (((pctActuals - pctGoal) / pctGoal) * 100).toFixed();
                    var extraClass = '';

                    if (pctString < 0) {
                        extraClass = 'text-danger';
                    } else if (pctString > 10) {
                        extraClass = 'bg-warning text-success';
                    }

                    html.push("<td class='text-center'><strong class='"+extraClass+"'>"+pctString+"%</strong></td>");
                }

            }
        html.push("</tr>");

        /*-------------------------------------------------------------------*/
        html.push(
            "<tr>",
                "<td></td>",
                "<td></td>",
                "<td></td>",
                "<td></td>",
                "<td></td>",
                "<td></td>",
                "<td></td>",
                "<td></td>",
                "<td></td>",
            "</tr>"
        );

        // console.log(headerData);

        /*-------------------------------------------------------------------*/
        // EMPLOYEE LOOP
        for (var emp=0; emp<empSummaries.length; emp++) {

            var empID = empSummaries[emp].empID;

            /*-------------------------------------------------------------------*/
            html.push(
                "<tr class='bg-info' style='border-top:6px solid #428bca;'>",
                    "<th>"+getEmpNameFromCode(empID, empMasterDatabase)+"</th>",
                    "<th class='text-center'>Sun</th>",
                    "<th class='text-center'>Mon</th>",
                    "<th class='text-center'>Tue</th>",
                    "<th class='text-center'>Wed</th>",
                    "<th class='text-center'>Thu</th>",
                    "<th class='text-center'>Fri</th>",
                    "<th class='text-center'>Sat</th>",
                    "<th class='text-center'>Totals</th>",
                 "</tr>"
            );
            html.push("<tr>");
                html.push("<td class='text-right'><strong>Schedule</strong></td>");
                for (var ioDay=0; ioDay<sched.schedule.length; ioDay++) {
                    var isScheduledToday = false;
                    for (var io=0; io<sched.schedule[ioDay].length; io++) {
                        if (empID === sched.schedule[ioDay][io].eid) {
                            isScheduledToday = true;
                            var empInOuts = sched.schedule[ioDay][io].inouts;
                            html.push("<td class='text-center'><ul class='inout-list'>");
                            for (var empInOut=0; empInOut<empInOuts.length; empInOut++) {
                                var inFormatted = inOutLabel("2000-01-01 " + empInOuts[empInOut].in + ":00");
                                var outFormatted = inOutLabel("2000-01-01 " + empInOuts[empInOut].out + ":00");
                                html.push("<li>" + inFormatted + " - " + outFormatted + "</li>");
                            }
                            html.push("</ul></td>");
                        }

                    }
                        if (! isScheduledToday) {
                            html.push("<td class='text-center'><strong>Off</strong></td>");
                        }
                }
                html.push("<td></td>");
            html.push("</tr>");

            /*-------------------------------------------------------------------*/
            html.push("<tr>");
                html.push("<td class='text-right'><strong>Total Hours</strong></td>");
                for (var thDay=0; thDay<7; thDay++) {
                    html.push("<td class='text-center'>"+empSummaries[emp].schedDays[thDay].hours+"</td>");
                }
                html.push("<td class='text-center'>"+empSummaries[emp].totalHours+"</td>");
            html.push("</tr>");

            /*-------------------------------------------------------------------*/
            html.push("<tr class='more-detail'>");
                html.push("<td class='text-right'><strong>Sales Goal</strong></td>");
                for (var sgDay=0; sgDay<7; sgDay++) {
                    html.push("<td class='text-center'>"+empSummaries[emp].schedDays[sgDay].scheduledSales.toFixed(2)+"</td>");
                }
                html.push("<td class='text-center'>"+empSummaries[emp].totalScheduledSales.toFixed(2)+"</td>");
            html.push("</tr>");

            /*-------------------------------------------------------------------*/
            html.push("<tr class='more-detail'>");
                html.push("<td class='text-right'><strong>Actual Sales</strong></td>");
                for (var acDay=0; acDay<7; acDay++) {
                    html.push("<td class='text-center'>"+empSummaries[emp].schedDays[acDay].actualSales.toFixed(2)+"</td>");
                }
                html.push("<td class='text-center'>"+empSummaries[emp].totalActualSales.toFixed(2)+"</td>");
            html.push("</tr>");

        }

        if (empSummaries.length === 0) {
            html.push("<tr>");
                html.push("<td colspan='100'><strong><em>No staff has been added to this schedule.</em></strong></td>");
            html.push("</tr>");
        }


        /*
        console.log("-------------------------------------------------");
        console.log("sched:");
        console.log(sched);

        console.log("targets:");
        console.log(targets);

        console.log("actuals:");
        console.log(actuals);

        console.log("daySummaries:");
        console.log(daySummaries);

        console.log("empSummaries:");
        console.log(empSummaries);
        console.log("-------------------------------------------------");
        */


        // Write the HTML
        $("#quickview").html(html.join(""));
        $(".more-detail").toggle();

        // TODO: !!!
        if ($("#ita").val()) {
            var close = $.ajax({
                url: "/lsvc/scheduler-csa",
                type: "GET"
            });
        } 
    });

});

$(document).on("click", "#toggle-detail", function(){
    $(".more-detail").toggle();
});
