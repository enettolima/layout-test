
function loadReport(storeNumber, reportDate)
{

    $("#budget-sales-plan").empty();
    $("#month-header").html("&mdash; " + moment(reportDate, "YYYY-MM").format("MMM YYYY"));
    $("#budget-sales-plan").html("<tr><td><em>Loading</em> <img src='/images/ajax-loader-arrows.gif'></td></tr>");

    var momMonth = moment(reportDate, "YYYY-MM");

    console.log(momMonth.format("MMM YYYY"));

    var reportRequest = $.ajax({
        url: "/lsvc/reports-budget-sales-plan/"+storeNumber+"/" + reportDate,
        type: "GET"
    });

    reportRequest.done(function(data){

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

            tr.pSales = parseFloat(tr.Sales).toFixed(2);

            if (isNaN(tr.pSales)) {
                tr.pSales = 'n/a';
            }

            tr.pBudget = parseFloat(tr.Budget).toFixed(2);

            if (isNaN(tr.pBudget)) {
                tr.pBudget = 'n/a';
            }

            tr.pDiff = parseFloat(tr.Diff).toFixed(2);

            if (isNaN(tr.pDiff)) {
                tr.pDiff = 'n/a';
            }

            tr.pPerBudClass = null;

            tr.pPerBud = parseFloat(tr.PerBud * 100).toFixed();

            if (isNaN(tr.pPerBud)) {
                tr.pPerBud = 'n/a';
            } else {
                if (tr.pPerBud < 0) {
                   tr.pPerBudClass = 'text-danger';
                } else if (tr.pPerBud > 10) {
                   tr.pPerBudClass = 'bg-warning text-success';
                }
            }

            html.push(
                "<tr>",

                    "<td class='text-right'>"+tr.dateMoment.format("ddd MM/DD/YY")+"</td>",
                    "<td class='text-right'>"+tr.pSales+"</td>",
                    "<td class='text-right'>"+tr.pBudget+"</td>",
                    "<td class='text-right'>"+tr.pDiff+"</td>",
                    "<td class='text-right'><strong class='"+tr.pPerBudClass+"'>"+tr.pPerBud+"%</strong></td>",
                "</tr>"
            );
        }

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

