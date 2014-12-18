$(document).ready(function(){

    var storeNumber = $("#storeNumber").val();
    var reportDate = $("#reportDate").val();

    var reportRequest = $.ajax({
        url: "/lsvc/reports-budget-sales-plan/"+storeNumber+"/" + reportDate,
        type: "GET"
    });

    reportRequest.done(function(data){

        var html = [];

        /*-------------------------------------------------------------------*/
        html.push(
            "<tr>",
                "<td>BDATE</td>",
                "<td>STORE_NO</td>",
                "<td>STORE_CODE</td>",
                "<td>DM</td>",
                "<td>SALES</td>",
                "<td>Budget</td>",
                "<td>Diff</td>",
                "<td>PerBud</td>",
            "</tr>"
        );

        for (var row=0; row<data.details.length; row++) 
        {
            var tr = data.details[row];

            tr.dateMoment = moment(tr.BDATE, "MMM D YYYY");

            html.push(
                "<tr>",

                    "<td>"+tr.dateMoment.format("ddd MMM do")+"</td>",

                    "<td>"+tr.STORE_NO+"</td>",
                    "<td>"+tr.STORE_CODE+"</td>",
                    "<td>"+tr.DM+"</td>",
                    "<td>"+tr.Sales+"</td>",
                    "<td>"+tr.Budget+"</td>",
                    "<td>"+tr.Diff+"</td>",
                    "<td>"+tr.PerBud+"</td>",
                "</tr>"
            );
        }

        $("#budget-sales-plan").html(html.join(''));
    });

});
