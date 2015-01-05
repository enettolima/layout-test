var monthsPast = 12;
var monthsFuture = 3;

function populateDropdowns()
{
    console.log('Populating Dropdowns');

    var m = new moment();
    var addMonths = 0;

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

    var reportRangeControl = $("#allstar-report-range");
    reportRangeControl.val(asRangeType);

    var cookieMoment = new moment().add(1, 'day');

    $.cookie('asRangeType', asRangeType, {expires: cookieMoment.toDate()});

    $.cookie('asRangeVal', asRangeVal, {expires: cookieMoment.toDate()});

    switch(asRangeType) {

        case 'month':

            $("#allstar-month").val(asRangeVal);

            $("#allstar-options-month").show();

            var monthMoment = new moment(asRangeVal, "YYYY-MM");

            $("#report-header").html("All Star | Store "+storeNumber+" | " + monthMoment.format("MMM") + " " + monthMoment.format("YYYY"));

            break;

        case 'week':
            console.log('asdfasfd');
            break;

        case 'date':
            console.log('hey it is a date');
            break;

        default:
            console.log('FDFFFFFF');
            break;

    }

    console.log("Load " + asRangeType + " report for " + asRangeVal + " for store " + storeNumber);

    var reportRequest = $.ajax({
        url: "/lsvc/reports-all-star/"+storeNumber+"/" + asRangeType + "/" + asRangeVal,
        type: "GET"
    });

    reportRequest.done(function(data){
        console.log(data);

        var html = [];

        html.push(
            "<tr>",
                "<td class='text-right'><strong>Employee</strong></td>",

                "<td class='text-right'><strong>Sales</strong></td>",

                "<td class='text-right'><strong>Target</strong></td>",

                "<td class='text-right'><strong>Diff</strong></td>",

                "<td class='text-right'><strong>ADS</strong></td>",

                "<td class='text-right'><strong>Hours</strong></td>",

                // These are always the same. Maybe they should be displayed outside of table?
                // "<td class='text-right'><strong>MonthBudgetAmt</strong></td>",
                // "<td class='text-right'><strong>MonthSales</strong></td>",
                // "<td class='text-right'><strong>StrAboveMonthGoal</strong></td>",
                // "<td class='text-right'><strong>Store_Code</strong></td>",

                "<td class='text-right' nowrap><strong>Sales W/O Hours</strong></td>",

                "<td class='text-right'><strong>UPT</strong></td>",
            "</tr>"
        );

        for (var row=0; row<data.details.length; row++) 
        {
            var tr = data.details[row];

            tr.pDiffBud = parseCurrency(tr.DiffBud);
            tr.pADS = parseCurrency(tr.ADS);
            tr.pEmpTarget = parseCurrency(tr.EmpTarget);
            tr.pHours = parseNum(tr.Hours, 0);

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
                    "<td class='text-right'>"+tr.pDiffBud.parsed+"</td>",

                    // "<td class='text-right'>"+tr.ADS+"</td>",
                    "<td class='text-right "+ ((tr.pADS.isNegative) ? "text-danger" : "")+"'>"+tr.pADS.parsed+"</td>",

                    // "<td class='text-right'>"+tr.Hours+"</td>",
                    "<td class='text-right'>"+tr.pHours.parsed+"</td>",

                    // These are always the same. Maybe they should be displayed outside of table?
                    // "<td class='text-right'>"+tr.MonthBudgetAmt+"</td>",
                    // "<td class='text-right'>"+tr.MonthSales+"</td>",
                    // "<td class='text-right'>"+tr.StrAboveMonthGoal+"</td>",


                    // "<td class='text-right'>"+tr.SalesWoHours+"</td>",
                    "<td class='text-right'"+ ((tr.pSalesWoHours.isNegative) ? "text-danger" : "")+"'>"+tr.pSalesWoHours.parsed+"</td>",
                    // "<td class='text-right'>"+tr.Store_Code+"</td>",

                    //"<td class='text-right'>"+tr.UPT+"</td>",
                    "<td class='text-right'>"+tr.pUPT.parsed+"</td>",
                "</tr>"
            );

        }

        var tots = data.totals[0];

        tots.pDiffBud = parseCurrency(tots.DiffBud);
        tots.pADS = parseCurrency(tots.ADS);
        tots.pEmpTarget = parseCurrency(tots.EmpTarget);
        tots.pHours = parseNum(tots.Hours, 0);

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
                "<td class='text-right'>"+tots.pDiffBud.parsed+"</td>",

                // "<td class='text-right'>"+tr.ADS+"</td>",
                "<td class='text-right "+ ((tots.pADS.isNegative) ? "text-danger" : "")+"'>"+tots.pADS.parsed+"</td>",

                // "<td class='text-right'>"+tr.Hours+"</td>",
                "<td class='text-right'>"+tots.pHours.parsed+"</td>",

                // These are always the same. Maybe they should be displayed outside of table?
                // "<td class='text-right'>"+tr.MonthBudgetAmt+"</td>",
                // "<td class='text-right'>"+tr.MonthSales+"</td>",
                // "<td class='text-right'>"+tr.StrAboveMonthGoal+"</td>",


                // "<td class='text-right'>"+tr.SalesWoHours+"</td>",
                "<td class='text-right'"+ ((tots.pSalesWoHours.isNegative) ? "text-danger" : "")+"'>"+tots.pSalesWoHours.parsed+"</td>",
                // "<td class='text-right'>"+tr.Store_Code+"</td>",
                "<td class='text-right'>"+tots.pUPT.parsed+"</td>",
            "</tr>"
        );

        $("#report-data").html(html.join(''));

    });


}

$(document).ready(function(){

    populateDropdowns();

    var asRangeType = null;
    var asRangeVal = null;

    var storeNumber = $("#storeNumber").val();

    if ($.cookie('asRangeType')) {
        asRangeType = $.cookie('asRangeType');
    } else {
        asRangeType = 'month';
    }

    if (asRangeType === 'month') {
        if ($.cookie('asRangeVal')) {
            asRangeVal = $.cookie('asRangeVal');
        } else {
            var m = new moment();
            asRangeVal = m.format("YYYY-MM");
        }
    } else if (asRangeType === 'week') {
        asRangeVal = $.cookie('asRangeVal');
    } else if (asRangeType === 'date') {
        asRangeType = $.cookie('reportDate');
    }

    loadReport(storeNumber, asRangeType, asRangeVal);

    $("#allstar-report-range").change(function(){

        $(".allstar-options").hide();

        var reportRange = $("#allstar-report-range").val();

        if (reportRange == 'month') {
            $("#allstar-options-month").show();
            $("#allstar-options-run").show();
        } else if(reportRange == 'week') {
            $("#allstar-options-week").show();
            $("#allstar-options-run").show();
        }
    });

    $("#allstar-options-month").change(function(){
        $("#allstar-options-run").show();
    });

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
