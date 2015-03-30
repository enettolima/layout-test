var monthsPast = 12;
var monthsFuture = 1;

function makeWeekLabel(moment) {
    return moment.format('WW-YYYY') + " (Ends " + moment.format('ddd M/D/YY') + ")";
}

function populateAllDropdowns()
{
    console.log('Populating Dropdowns');
    populateMonthDropdown();
    populateWeekDropdown();
}

/*
 * This does not work correctly at the year boundaries, but moving 
 * past it at this point because it's becoming a waste of time.
 *
 * TODO: Reasses the entire logic of reporting on WW-YYYY when there's some down time
 */
function populateWeekDropdown()
{
    var momentNow = new moment().endOf('week');

    var momentPast = new moment().subtract(1, 'years').endOf('week');

    momentPast.subtract(1, 'week'); // This is for convenience and readability of the following loop: 

    while (momentPast.format('WW-YYYY') !== momentNow.format('WW-YYYY')) {

        momentPast.add(1, 'week');

        var weekVal = momentPast.format('WW-YYYY');
        //var weekLabel = momentPast.format('WW-YYYY') + " (Ending " + momentPast.format('ddd MMM Do YYYY') + ")";
        var weekLabel = makeWeekLabel(momentPast);

        $("#allstar-week").append($("<option />").attr('value', weekVal).text(weekLabel));

    }
}

function populateMonthDropdown()
{
    var m = new moment();

    m.subtract(12, 'months');

    for (var month=0; month<(monthsPast + monthsFuture); month++) {

        var thisMonth = m.format("YYYY-MM");

        $("#allstar-month").append($("<option />").attr('value', thisMonth).text(m.format("MMM YYYY")));

        m.add(1, 'months');
    }
}

function loadReport(storeNumber, asRangeType, asRangeVal){

    $("#allstar-options-run").hide();

    if (storeNumber === '000') {
        $("#report-header").html("Invalid Store");
        $("#report-data").html("<tr><td><em>Unable to run report for Store "+storeNumber+".</em></td></tr>");
        return;
    }

    // $("#report-header").html("Loady Reporty");
    $("#report-data").html("<tr><td><em>Loading</em> <img src='/images/ajax-loader-arrows.gif'></td></tr>");
    $("#report-secondary").html("");

    var reportRangeControl = $("#allstar-report-range");
    reportRangeControl.val(asRangeType);

    var cookieMoment = new moment().add(1, 'day');

    $.cookie('asRangeType', asRangeType, {expires: cookieMoment.toDate()});

    $.cookie('asRangeVal', asRangeVal, {expires: cookieMoment.toDate()});

    switch(asRangeType) {

        case 'month':

            // This might be already set as so, but just in case...
            $("#allstar-month").val(asRangeVal);
            $("#allstar-options-month").show();

            var monthMoment = new moment(asRangeVal, "YYYY-MM");

            $("#report-header").html("All Star | Store "+storeNumber+" | " + monthMoment.format("MMM") + " " + monthMoment.format("YYYY"));

            break;

        case 'week':
            $("#allstar-week").val(asRangeVal);
            $("#allstar-options-week").show();
            var weekMoment = new moment(asRangeVal, "WW-YYYY").endOf('week');
            var weekLabel = makeWeekLabel(weekMoment);
            $("#report-header").html("All Star | Store "+storeNumber+" | Week " + weekLabel);
            break;

        case 'date':
            console.log('hey it is a date');
            break;

        default:
            console.log('FDFFFFFF');
            break;

    }

    // console.log("Load " + asRangeType + " report for " + asRangeVal + " for store " + storeNumber);

    var url  = "/lsvc/reports-all-star/"+storeNumber+"/"+asRangeType+"/"+asRangeVal;

    var reportRequest = $.ajax({
        url: url,
        type: "GET"
    });

    reportRequest.done(function(data){

        var html = [];

        var secondaryHtml = [];
        secondaryHtml.push("<thead><tr><th colspan='2'>Month Summary</th></tr></thead>");

        if (data.details.length === 0 || data.totals.length === 0) {
            html.push("<tr><td><em>No data available for this time period.</em></td></tr>");
            $("#report-data").html(html.join(''));
            return;
        }

        html.push(
            "<thead>",
            "<tr>",
                "<th class='text-right'><strong>Employee</strong></th>",

                "<th class='text-right'><strong>Sales</strong></th>",

                "<th class='text-right'><strong>Target</strong></th>",

                "<th class='text-right'><strong>Diff</strong></th>",

                "<th class='text-right'><strong>PCT%</strong></th>",

                "<th class='text-right'><strong>ADS</strong></th>",

                "<th class='text-right'><strong>UPT</strong></th>",

                "<th class='text-right'><strong>Hours</strong></th>",

                // These are always the same. Maybe they should be displayed outside of table?
                // "<td class='text-right'><strong>MonthBudgetAmt</strong></td>",
                // "<td class='text-right'><strong>MonthSales</strong></td>",
                // "<td class='text-right'><strong>StrAboveMonthGoal</strong></td>",
                // "<td class='text-right'><strong>Store_Code</strong></td>",

                "<th class='text-right' nowrap><strong>W/O Hours</strong></th>",

            "</tr>",
            "</thead>"
        );

        html.push("<tbody>");

        for (var row=0; row<data.details.length; row++) 
        {

            var tr = data.details[row];

            if (row === 0) {
                tr.pMonthBudgetAmt = parseCurrency(tr.MonthBudgetAmt);
                tr.pMonthSales = parseCurrency(tr.MonthSales);
                tr.pPct = parsePct((tr.MonthSales - tr.MonthBudgetAmt) / tr.MonthBudgetAmt);
                tr.pDiff = parseCurrency(tr.MonthSales - tr.MonthBudgetAmt);

                secondaryHtml.push("<tbody>");
                secondaryHtml.push("<tr><td><strong>Budget:</strong></td><td class='text-right'> " + tr.pMonthBudgetAmt.parsed + "</td></tr>");
                secondaryHtml.push("<tr><td><strong>Sales:</strong></td><td class='text-right'> " + tr.pMonthSales.parsed + "</td></tr>");
                secondaryHtml.push("<tr><td><strong>Diff:</strong></td><td class='text-right'> " + tr.pDiff.parsed + "</td></tr>");
                secondaryHtml.push("<tr><td><strong>Diff PCT:</strong></td><td class='text-right'> " + tr.pPct.parsed + "</td></tr>");
                secondaryHtml.push("<tr><td><strong>Above Goal?:</strong></td><td class='text-right'> " + tr.StrAboveMonthGoal + "</td></tr>");
                secondaryHtml.push("</tbody>");
            }

            tr.pDiffBud = parseCurrency(tr.DiffBud);
            tr.pDiffPct = parsePct((tr.Sales - tr.EmpTarget) / tr.EmpTarget);
            tr.pADS = parseCurrency(tr.ADS);
            tr.pEmpTarget = parseCurrency(tr.EmpTarget);
            tr.pHours = parseNum(tr.Hours);

            tr.pSales = parseCurrency(tr.Sales);
            tr.pSalesWoHours = parseCurrency(tr.SalesWoHours);
            tr.pUPT = parseNum(tr.UPT);

            html.push(
                "<tr>",
                    "<td nowrap class='text-right'>"+tr.RPRO_FULL_NAME+"</td>",

                    // "<td class='text-right'>"+tr.Sales+"</td>",
                    "<td class='text-right "+ ((tr.pSales.isNegative) ? "text-danger" : "")+"'>"+tr.pSales.parsed+"</td>",

                    // "<td class='text-right'>"+tr.EmpTarget+"</td>",
                    "<td class='text-right'>"+tr.pEmpTarget.parsed+"</td>",

                    // "<td class='text-right'>"+tr.DiffBud+"</td>",
                    "<td class='text-right "+ ((tr.pDiffBud.isNegative) ? "text-danger" : "")+"'>"+tr.pDiffBud.parsed+"</td>",

                    // "<td class='text-right'>"+tr.DiffBud+"</td>",
                    "<td class='text-right "+ ((tr.pDiffPct.isNegative) ? "text-danger" : "")+"'>"+tr.pDiffPct.parsed+"</td>",

                    // "<td class='text-right'>"+tr.ADS+"</td>",
                    "<td class='text-right "+ ((tr.pADS.isNegative) ? "text-danger" : "")+"'>"+tr.pADS.parsed+"</td>",

                    //"<td class='text-right'>"+tr.UPT+"</td>",
                    "<td class='text-right'>"+tr.pUPT.parsed+"</td>",

                    // "<td class='text-right'>"+tr.Hours+"</td>",
                    "<td class='text-right'>"+tr.pHours.parsed+"</td>",

                    // These are always the same. Maybe they should be displayed outside of table?
                    // "<td class='text-right'>"+tr.MonthBudgetAmt+"</td>",
                    // "<td class='text-right'>"+tr.MonthSales+"</td>",
                    // "<td class='text-right'>"+tr.StrAboveMonthGoal+"</td>",

                    // "<td class='text-right'>"+tr.SalesWoHours+"</td>",
                    "<td class='text-right "+ ((tr.pSalesWoHours.isNegative) ? "text-danger" : "")+"'>"+tr.pSalesWoHours.parsed+"</td>",
                    // "<td class='text-right'>"+tr.Store_Code+"</td>",

                "</tr>"
            );
        }

        html.push("</tbody><tfoot>");

        var tots = data.totals[0];

        tots.pDiffBud = parseCurrency(tots.DiffBud);
        tots.pADS = parseCurrency(tots.ADS);
        tots.pEmpTarget = parseCurrency(tots.EmpTarget);
        tots.pHours = parseNum(tots.Hours);

        tots.pSales = parseCurrency(tots.Sales);
        tots.pSalesWoHours = parseCurrency(tots.SalesWoHours);
        tots.pUPT = parseNum(tots.UPT);

        html.push(
            "<tr>",
                "<td nowrap class='text-right'><strong>TOTALS:</strong></td>",

                // "<td class='text-right'>"+tr.Sales+"</td>",
                "<td class='text-right "+ ((tots.pSales.isNegative) ? "text-danger" : "")+"'>"+tots.pSales.parsed+"</td>",

                // "<td class='text-right'>"+tr.EmpTarget+"</td>",
                "<td class='text-right'>"+tots.pEmpTarget.parsed+"</td>",

                // "<td class='text-right'>"+tr.DiffBud+"</td>",
                "<td class='text-right "+ ((tots.pDiffBud.isNegative) ? "text-danger" : "")+"'>"+tots.pDiffBud.parsed+"</td>",

                // "<td class='text-right'>"+tr.ADS+"</td>",
                "<td class='text-right'></td>",

                // "<td class='text-right'>"+tr.ADS+"</td>",
                "<td class='text-right "+ ((tots.pADS.isNegative) ? "text-danger" : "")+"'>"+tots.pADS.parsed+"</td>",

                // "<td class='text-right'>"+tr.Store_Code+"</td>",
                "<td class='text-right'>"+tots.pUPT.parsed+"</td>",

                // "<td class='text-right'>"+tr.Hours+"</td>",
                "<td class='text-right'>"+tots.pHours.parsed+"</td>",

                // These are always the same. Maybe they should be displayed outside of table?
                // "<td class='text-right'>"+tr.MonthBudgetAmt+"</td>",
                // "<td class='text-right'>"+tr.MonthSales+"</td>",
                // "<td class='text-right'>"+tr.StrAboveMonthGoal+"</td>",


                // "<td class='text-right'>"+tr.SalesWoHours+"</td>",
                "<td class='text-right "+ ((tots.pSalesWoHours.isNegative) ? "text-danger" : "")+"'>"+tots.pSalesWoHours.parsed+"</td>",
            "</tr>"
        );

        html.push("</tfoot>");

        $("#report-data").html(html.join(''));
        $("#report-data").tablesorter({
            sortList : [[0,0]],
        });

        $("#report-secondary").html(secondaryHtml.join(''));

    });
}

$(document).ready(function(){

    populateAllDropdowns();

    var asRangeType = null;
    var asRangeVal = null;

    var storeNumber = $("#storeNumber").val();

    if ($.cookie('asRangeType')) {
        asRangeType = $.cookie('asRangeType');
    } else {
        asRangeType = 'month';
    }

    var mNow = new moment();

    if (asRangeType === 'month') {
        if ($.cookie('asRangeVal')) {
            asRangeVal = $.cookie('asRangeVal');
        } else {
            asRangeVal = mNow.format("YYYY-MM");
        }
    } else if (asRangeType === 'week') {
        if ($.cookie('asRangeVal')) {
            asRangeVal = $.cookie('asRangeVal');
        } else {
            asRangeVal = mNow.format("WW-YYYY");
        }
    } else if (asRangeType === 'date') {
        asRangeType = $.cookie('reportDate');
    }

    loadReport(storeNumber, asRangeType, asRangeVal);


    $("#allstar-report-range").change(function(){

        $(".allstar-options").hide();

        var reportRange = $("#allstar-report-range").val();

        if (reportRange == 'month') {

            if (asRangeType === 'month') {
                $("#allstar-month").val(asRangeVal);
            } else {
                $("#allstar-month").val(mNow.format("YYYY-MM"));
            }

            $("#allstar-options-month").show();
            $("#allstar-options-run").show();

        } else if(reportRange == 'week') {

            if (asRangeType === 'week') {
                $("#allstar-week").val(asRangeVal);
            } else {
                $("#allstar-week").val(mNow.format("WW-YYYY"));
            }

            $("#allstar-options-week").show();
            $("#allstar-options-run").show();
        }
    });

    $("#allstar-month").change(function(){
        $("#allstar-options-run").show();
    });

    $("#allstar-week").change(function(){
        $("#allstar-options-run").show();
    });

    // Run the report
    $("#allstar-options-run").on('click', function(){

        asRangeType = $("#allstar-report-range").val();

        switch (asRangeType) {
            case 'month':
                asRangeVal = $("#allstar-month").val();
            break;
            case 'week':
                asRangeVal = $("#allstar-week").val();
            break;
        }

        loadReport(storeNumber, asRangeType, asRangeVal);
    });
});
