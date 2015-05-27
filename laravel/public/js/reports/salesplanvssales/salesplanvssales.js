function loadReport(storeNumber)
{

	$("#report-header-month").empty();
    $("#budget-sales-plan").empty();

    if (storeNumber === '000') {
        $("#report-header").html("Invalid Store");
        $("#report-data").html("<tr><td><em>Unable to run report for Store "+storeNumber+".</em></td></tr>");
        return;
    }

    $("#repot-data").html("<tr><td><em>Loading</em> <img src='/images/ajax-loader-arrows.gif'></td></tr>");

    var reportRequest = $.ajax({
        url: "/lsvc/reports-sales-plan-vs-sales/"+storeNumber,
        type: "GET"
    });

    reportRequest.done(function(response){

		$("#report-store-number").html(storeNumber);

        var html = [];

        /*-------------------------------------------------------------------*/
        html.push(
            "<tr>",
                "<td class='text-right'><strong>Budget</strong></td>",
                "<td class='text-right'><strong>DM</strong></td>",
                "<td class='text-right'><strong>DM&nbsp;%&nbsp;to&nbsp;Budget</strong></td>",
                "<td class='text-right'><strong>DM&nbsp;Total&nbsp;Diff</strong></td>",
                "<td class='text-right'><strong>DM&nbsp;Total&nbsp;Budget</strong></td>",
                "<td class='text-right'><strong>DM&nbsp;Total&nbsp;Sales</strong></td>",
                "<td class='text-right'><strong>LY&nbsp;Sales&nbsp;MTD</strong></td>",
                "<td class='text-right'><strong>Month</strong></td>",
                "<td class='text-right'><strong>REG&nbsp;Amt&nbsp;Diff</strong></td>",
                "<td class='text-right'><strong>REG&nbsp;Budget&nbsp;MTD</strong></td>",
                "<td class='text-right'><strong>REG&nbsp;%&nbsp;to&nbsp;Budget</strong></td>",
                "<td class='text-right'><strong>REG&nbsp;Sales</strong></td>",
                "<td class='text-right'><strong>RM</strong></td>",
                "<td class='text-right'><strong>Sales</strong></td>",
                "<td class='text-right'><strong>Total&nbsp;Budget</strong></td>",
            "</tr>"
        );

        for (var row=0; row<response.data.length; row++) 
        {
            var tr = response.data[row];

            tr.pBUDGET = parseCurrency(tr.BUDGET);

            tr.pDM_PER_TO_BUDGET = parsePct(tr.DM_PER_TO_BUDGET);

            tr.pDM_TOTAL_AMT_DIFF = parseCurrency(tr.DM_TOTAL_AMT_DIFF);

            tr.pDM_TOTAL_BUDGET = parseCurrency(tr.DM_TOTAL_BUDGET);

            tr.pDM_TOTAL_SALES = parseCurrency(tr.DM_TOTAL_SALES);

            tr.pLY_SALES_MTD = parseCurrency(tr.LY_SALES_MTD);

            tr.pREGION_AMT_DIFF = parseCurrency(tr.REGION_AMT_DIFF);

            tr.pREGION_BUDGET_MTD = parseCurrency(tr.REGION_BUDGET_MTD);

            tr.pREGION_PER_TO_BUDGET = parsePct(tr.REGION_PER_TO_BUDGET);

            tr.pREGION_SALES = parseCurrency(tr.REGION_SALES);

            tr.pSALES = parseCurrency(tr.SALES);

            tr.pTOTAL_BUDGET = parseCurrency(tr.TOTAL_BUDGET);

            // tr.dateMoment = moment(tr.BDATE, "MMM D YYYY");

            // tr.pSales = parseCurrency(tr.Sales);

            // tr.pBudget = parseCurrency(tr.Budget);

            // tr.pDiff = parseCurrency(tr.Diff);

            // tr.pPerBudClass = null;

			// tr.pctParsed = parsePct(tr.PerBud);

            html.push(
                "<tr>",
                    // "<td class='text-right'>"+tr.dateMoment.format("ddd MM/DD/YY")+"</td>",
                    // "<td class='text-right'>"+tr.pSales.parsed+"</td>",
                    // "<td class='text-right'>"+tr.pBudget.parsed+"</td>",
                    // "<td class='text-right "+ ((tr.pDiff.isNegative) ? "text-danger" : "") +"'>"+tr.pDiff.parsed+"</td>",
                    // "<td class='text-right'><strong class='"+((tr.pctParsed.isNegative) ? "text-danger" : "bg-warning text-success")+"'>"+tr.pctParsed.parsed+"</strong></td>",

                    "<td class='text-right'>"+tr.pBUDGET.parsed+"</td>",
                    "<td class='text-right'>"+tr.DM+"</td>",
                    "<td class='text-right'>"+tr.pDM_PER_TO_BUDGET.parsed+"</td>",
                    "<td class='text-right'>"+tr.pDM_TOTAL_AMT_DIFF.parsed+"</td>",
                    "<td class='text-right'>"+tr.pDM_TOTAL_BUDGET.parsed+"</td>",
                    "<td class='text-right'>"+tr.pDM_TOTAL_SALES.parsed+"</td>",
                    "<td class='text-right'>"+tr.pLY_SALES_MTD.parsed+"</td>",
                    "<td class='text-right'>"+tr.Month+"/"+tr.Year+"</td>",
                    "<td class='text-right "+ ((tr.pREGION_AMT_DIFF.isNegative) ? "text-danger" : "") +"'>"+tr.pREGION_AMT_DIFF.parsed+"</td>",
                    "<td class='text-right'>"+tr.pREGION_BUDGET_MTD.parsed+"</td>",
                    "<td class='text-right'><span class='"+((tr.pREGION_PER_TO_BUDGET.isNegative) ? "text-danger" : "text-success")+"'>"+tr.pREGION_PER_TO_BUDGET.parsed+"</span></td>",
                    "<td class='text-right'>"+tr.pREGION_SALES.parsed+"</td>",
                    "<td class='text-right'>"+tr.RM+"</td>",
                    "<td class='text-right'>"+tr.pSALES.parsed+"</td>",
                    "<td class='text-right'>"+tr.pTOTAL_BUDGET.parsed+"</td>",
                "</tr>"
            );
        }

		// var tots = data.totals[0];

		// tots.pSales = parseCurrency(tots.Sales);
		// tots.pBudget = parseCurrency(tots.Budget);
		// tots.pDiff = parseCurrency(tots.Diff);

		// tots.pPerMonth = parsePct(tots.PerMonth);
		// tots.pPerMTD = parsePct(tots.PerMTD);

        /*
        html.push(
            "<tr>",
                "<td class='text-right'><strong>Totals:</strong></td>",
                "<td class='text-right'>"+tots.pSales.parsed+"</td>",
                "<td class='text-right'>"+tots.pBudget.parsed+"</td>",
                "<td class='text-right "+ ((tots.pDiff.isNegative) ? "text-danger" : "") +"'>"+tots.pDiff.parsed+"</td>",
                "<td class='text-right'>",
                    "<strong class='"+((tots.pPerMonth.isNegative) ? "text-danger" : "bg-warning text-success")+"'>"+tots.pPerMonth.parsed+"</strong> ",
                    "<strong class='"+((tots.pPerMTD.isNegative) ? "text-danger" : "bg-warning text-success")+"'>(MTD: "+tots.pPerMTD.parsed+")</strong>",
                "</td>",
            "</tr>"
        );
       */


        $("#report-data").html(html.join(''));
    });

}

$(document).ready(function(){

    var storeNumber = $("#storeNumber").val();

    loadReport(storeNumber);

});
