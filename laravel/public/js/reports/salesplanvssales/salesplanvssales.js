function loadReport(storeNumber)
{

	$("#report-header-month").empty();
    $(".report-table").empty();

    if (storeNumber === '000') {
        $("#report-header").html("Invalid Store");
        $("#summary-report-data").html("<tr><td><em>Unable to run report for Store "+storeNumber+".</em></td></tr>");
        return;
    }

    $("#summary-report-data").html("<tr><td><em>Loading</em> <img src='/images/ajax-loader-arrows.gif'></td></tr>");

    var reportRequest = $.ajax({
        url: "/lsvc/reports-sales-plan-vs-sales/"+storeNumber,
        type: "GET"
    });

    reportRequest.done(function(response){

		$("#report-store-number").html(storeNumber);

        var summaryHTML = [];
        var rmHTML = [];
        var dmHTML = [];

        /*-------------------------------------------------------------------*/
        summaryHTML.push(
            "<tr>",
                "<td class='text-right'><strong>Month</strong></td>",
                "<td class='text-right'><strong>Sales</strong></td>",
                "<td class='text-right'><strong>Budget</strong></td>",
                "<td class='text-right'><strong>LY&nbsp;Sales&nbsp;MTD</strong></td>",
                "<td class='text-right'><strong>Total&nbsp;Budget</strong></td>",
            "</tr>"
        );

        rmHTML.push(
            "<tr>",
                "<td class='text-right'><strong>Month</strong></td>",
                "<td class='text-right'><strong>RM</strong></td>",
                "<td class='text-right'><strong>Budget&nbsp;MTD</strong></td>",
                "<td class='text-right'><strong>Sales</strong></td>",
                "<td class='text-right'><strong>Difference</strong></td>",
                "<td class='text-right'><strong>%&nbsp;to&nbsp;Budget</strong></td>",
            "</tr>"
        );

        dmHTML.push(
            "<tr>",
                "<td class='text-right'><strong>Month</strong></td>",
                "<td class='text-right'><strong>DM</strong></td>",
                "<td class='text-right'><strong>Total&nbsp;Budget</strong></td>",
                "<td class='text-right'><strong>Total&nbsp;Sales</strong></td>",
                "<td class='text-right'><strong>Total&nbsp;Diff</strong></td>",
                "<td class='text-right'><strong>%&nbsp;to&nbsp;Budget</strong></td>",
            "</tr>"
        );

        for (var row=0; row<response.data.length; row++) 
        {
            var tr = response.data[row];

            tr.pSALES        = parseCurrency(tr.SALES);
            tr.pLY_SALES_MTD = parseCurrency(tr.LY_SALES_MTD);
            tr.pBUDGET       = parseCurrency(tr.BUDGET);
            tr.pTOTAL_BUDGET = parseCurrency(tr.TOTAL_BUDGET);

            tr.pREGION_BUDGET_MTD    = parseCurrency(tr.REGION_BUDGET_MTD);
            tr.pREGION_SALES         = parseCurrency(tr.REGION_SALES);
            tr.pREGION_AMT_DIFF      = parseCurrency(tr.REGION_AMT_DIFF);
            tr.pREGION_PER_TO_BUDGET = parsePct(tr.REGION_PER_TO_BUDGET);

            tr.pDM_TOTAL_BUDGET   = parseCurrency(tr.DM_TOTAL_BUDGET);
            tr.pDM_TOTAL_SALES    = parseCurrency(tr.DM_TOTAL_SALES);
            tr.pDM_TOTAL_AMT_DIFF = parseCurrency(tr.DM_TOTAL_AMT_DIFF);
            tr.pDM_PER_TO_BUDGET  = parsePct(tr.DM_PER_TO_BUDGET);

            summaryHTML.push(
                "<tr>",
                    "<td class='text-right'>"+tr.Month+"/"+tr.Year+"</td>",
                    "<td class='text-right'>"+tr.pSALES.parsed+"</td>",
                    "<td class='text-right'>"+tr.pLY_SALES_MTD.parsed+"</td>",
                    "<td class='text-right'>"+tr.pBUDGET.parsed+"</td>",
                    "<td class='text-right'>"+tr.pTOTAL_BUDGET.parsed+"</td>",
                "</tr>"
            );

            rmHTML.push(
                "<tr>",
                    "<td class='text-right'>"+tr.Month+"/"+tr.Year+"</td>",
                    "<td class='text-right'>"+tr.RM+"</td>",
                    "<td class='text-right'>"+tr.pREGION_BUDGET_MTD.parsed+"</td>",
                    "<td class='text-right'>"+tr.pREGION_SALES.parsed+"</td>",
                    "<td class='text-right "+ ((tr.pREGION_AMT_DIFF.isNegative) ? "text-danger" : "text-success") +"'>"+tr.pREGION_AMT_DIFF.parsed+"</td>",
                    "<td class='text-right "+ ((tr.pREGION_PER_TO_BUDGET.isNegative) ? "text-danger" : "text-success") +"'>"+tr.pREGION_PER_TO_BUDGET.parsedDec+"</td>",
                "</tr>"
            );

            dmHTML.push(
                "<tr>",
                    "<td class='text-right'>"+tr.Month+"/"+tr.Year+"</td>",
                    "<td class='text-right'>"+tr.DM+"</td>",
                    "<td class='text-right'>"+tr.pDM_TOTAL_BUDGET.parsed+"</td>",
                    "<td class='text-right'>"+tr.pDM_TOTAL_SALES.parsed+"</td>",
                    "<td class='text-right "+ ((tr.pDM_TOTAL_AMT_DIFF.isNegative) ? "text-danger" : "text-success") +"'>"+tr.pDM_TOTAL_AMT_DIFF.parsed+"</td>",
                    "<td class='text-right "+ ((tr.pDM_PER_TO_BUDGET.isNegative) ? "text-danger" : "text-success") +"'>"+tr.pDM_PER_TO_BUDGET.parsedDec+"</td>",
                "</tr>"
            );
        }

        $("#summary-report-data").html(summaryHTML.join(''));
        $("#rm-report-data").html(rmHTML.join(''));
        $("#dm-report-data").html(dmHTML.join(''));

    });

}

$(document).ready(function(){

    var storeNumber = $("#storeNumber").val();

    loadReport(storeNumber);

});
