function loadReport(storeNumber, reportDate)
{

	$("#report-header-month").empty();
    $("#budget-sales-plan").empty();

    if (storeNumber === '000') {
        $("#report-header").html("Invalid Store");
        $("#budget-sales-plan").html("<tr><td><em>Unable to run report for Store "+storeNumber+".</em></td></tr>");
        return;
    }

    $("#budget-sales-plan").html("<tr><td><em>Loading</em> <img src='/images/ajax-loader-arrows.gif'></td></tr>");

    var momMonth = moment(reportDate, "YYYY-MM");

    var reportRequest = $.ajax({
        url: "/lsvc/reports-budget-sales-plan/"+storeNumber+"/" + reportDate,
        type: "GET"
    });

    reportRequest.done(function(data){

		$("#report-header-month").html(moment(reportDate, "YYYY-MM").format("MMM YYYY"));
		$("#report-header-dm").html(data.details[0].DM);

        var html = [];

        /*-------------------------------------------------------------------*/
        html.push(
            "<tr>",
                "<td class='text-right'><strong>Date</strong></td>",
                "<td class='text-right'><strong>Sales</strong></td>",
                "<td class='text-right'><strong>Budget</strong></td>",
                "<td class='text-right'><strong>Diff</strong></td>",
                "<td class='text-right'><strong>Over/Under</strong></td>",
            "</tr>"
        );

        for (var row=0; row<data.details.length; row++) 
        {
            var tr = data.details[row];

            tr.dateMoment = moment(tr.BDATE, "MMM D YYYY");

            tr.pSales = parseCurrency(tr.Sales);

            tr.pBudget = parseCurrency(tr.Budget);

            tr.pDiff = parseCurrency(tr.Diff);

            tr.pPerBudClass = null;

			tr.pctParsed = parsePct(tr.PerBud)

            html.push(
                "<tr>",
                    "<td class='text-right'>"+tr.dateMoment.format("ddd MM/DD/YY")+"</td>",
                    "<td class='text-right'>"+tr.pSales.parsed+"</td>",
                    "<td class='text-right'>"+tr.pBudget.parsed+"</td>",
                    "<td class='text-right "+ ((tr.pDiff.isNegative) ? "text-danger" : "") +"'>"+tr.pDiff.parsed+"</td>",
                    "<td class='text-right'><strong class='"+((tr.pctParsed.isNegative) ? "text-danger" : "bg-warning text-success")+"'>"+tr.pctParsed.parsed+"</strong></td>",
                "</tr>"
            );
        }

		var tots = data.totals[0];

		tots.pSales = parseCurrency(tots.Sales);
		tots.pBudget = parseCurrency(tots.Budget);
		tots.pDiff = parseCurrency(tots.Diff);

		tots.pPerMonth = parsePct(tots.PerMonth);
		tots.pPerMTD = parsePct(tots.PerMTD);

		console.log(tots);

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


        $("#budget-sales-plan").html(html.join(''));
    });


}

$(document).ready(function(){

    var storeNumber = $("#storeNumber").val();

    var reportDate = $("#reportDate").val();

    loadReport(storeNumber, reportDate);

    $("#monthSelector").change(function(){
        loadReport(storeNumber, $(this).val());
    });

});

