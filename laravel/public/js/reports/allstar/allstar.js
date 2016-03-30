var monthsPast = 12;
var monthsFuture = 1;

function makeWeekLabel(label) {
  if(label.format('WW')>52){
    var currentYear = new moment().year();
    //var weekVal = momentPast.format('WW-'+currentYear);
    return label.format('WW-'+currentYear) + " (Ends " + label.format('ddd M/D/YY') + ")";
  }else{
    //var weekVal = momentPast.format('WW-YYYY');
    return label.format('WW-YYYY') + " (Ends " + label.format('ddd M/D/YY') + ")";
  }
    //return moment.format('WW-YYYY') + " (Ends " + moment.format('ddd M/D/YY') + ")";
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

    var currentYear = new moment().year();

    console.log("Moment now "+momentNow.format('WW-YYYY')
    +" -- Moment past "+momentPast.format('WW-YYYY')
    +" subtract "+momentPast.subtract(1, 'week').format('WW-YYYY')
    +" current year "+currentYear);

    while (momentPast.format('WW-YYYY') !== momentNow.format('WW-YYYY')) {

        momentPast.add(1, 'week');


        /*if(momentPast.year() > currentYear){
            var weekVal = momentPast.format('WW-'+currentYear);
        }else{

        }*/


        if(momentPast.format('WW')>52){
          var weekVal = momentPast.format('WW-'+currentYear);
        }else{
          var weekVal = momentPast.format('WW-YYYY');
        }
        console.log("Moment past week is "+momentPast.week()+" Week val "+weekVal+ " week val "+momentPast.format('WW'));
        //var weekLabel = momentPast.format('WW-YYYY') + " (Ending " + momentPast.format('ddd MMM Do YYYY') + ")";
        var weekLabel = makeWeekLabel(momentPast);

        $("#allstar-week").append($("<option />").attr('value', weekVal).text(weekLabel));

    }

    console.log("Moment past last week is "+momentPast.format('WW'));
    if(momentPast.format('WW')>52){
      momentPast.add(1, 'week');
      var weekVal = momentPast.format('WW-YYYY');
      var weekLabel = makeWeekLabel(momentPast);

      $("#allstar-week").append($("<option />").attr('value', weekVal).text(weekLabel));
      console.log("Inside if");
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


$(document).ready(function(){
    var asRangeType = 'range';
    var storeNumber = $("#storeNumber").val();
    $("#date-from").datepicker({
        beforeShow: function(input, inst)
        {
          // Handle calendar position before showing it.
          // It's not supported by Datepicker itself (for now) so we need to use its internal variables.
          var calendar = inst.dpDiv;

          // Dirty hack, but we can't do anything without it (for now, in jQuery UI 1.8.20)
          setTimeout(function() {
              calendar.position({
                  my: 'right top',
                  at: 'right bottom',
                  collision: 'none',
                  of: input
              });
          }, 1);
        }
    });
    $("#date-to").datepicker({
        beforeShow: function(input, inst)
        {
          // Handle calendar position before showing it.
          // It's not supported by Datepicker itself (for now) so we need to use its internal variables.
          var calendar = inst.dpDiv;

          // Dirty hack, but we can't do anything without it (for now, in jQuery UI 1.8.20)
          setTimeout(function() {
              calendar.position({
                  my: 'right top',
                  at: 'right bottom',
                  collision: 'none',
                  of: input
              });
          }, 1);
        }
    });

    // Pre populate fields with current week...
    var mNow = new moment();
    var momentFrom = mNow.day("Sunday");

    var mNow = new moment();
    var momentTo = mNow.day("Saturday");

    // But set that using previous settings if they exist
    if ($("#allStarLastFrom").val() && $("#allStarLastTo").val()){
        momentFrom = moment($("#allStarLastFrom").val(), "MM/DD/YYYY");
        momentTo = moment($("#allStarLastTo").val(), "MM/DD/YYYY");
    }

    $("#date-from").val(momentFrom.format("MM/DD/YYYY"));
    $("#date-to").val(momentTo.format("MM/DD/YYYY"));

    //Load report at first load
    loadReport(storeNumber, asRangeType, momentFrom, momentTo);

    // Run the report
    $("#allstar-run").on('click', function(){
      var fromVal   = $("#date-from").val();
      var toVal     = $("#date-to").val();
      var fromDate  = moment(fromVal, "MM-DD-YYYY");
      var toDate    = moment(toVal, "MM-DD-YYYY");

      loadReport(storeNumber, asRangeType, fromDate, toDate);
    });
});

function loadReport(storeNumber, asRangeType, asDateFrom, asDateTo){
    $("#allstar-run").hide();

    if (storeNumber === '000') {
        $("#report-header").html("Invalid Store");
        $("#report-data").html("<tr><td><em>Unable to run report for Store "+storeNumber+".</em></td></tr>");
        return;
    }

    $("#report-data").html("<tr><td><em>Loading</em> <img src='/images/ajax-loader-arrows.gif'></td></tr>");
    $("#report-secondary").html("");

    var fromTitle = new moment(asDateFrom).format("MM/DD/YYYY");
    var toTitle = new moment(asDateTo).format("MM/DD/YYYY");
    $("#report-header").html("All Star | Store "+storeNumber+" | From " + fromTitle +" to "+toTitle);
    // console.log("Load " + asRangeType + " report for " + asRangeVal + " for store " + storeNumber);

    //Removing / from the date so we can send on the URL on the ajax Request
    //***** VERY IMPORTANT *****
    //We can not use the moment format here cause it will calculate the time based on the
    //client's browser. Replace was necessary to prevent what was selected by the User
    var fromEncoded = fromTitle.replace( /\//g, "-");
    var toEncoded = toTitle.replace( /\//g, "-");
    //console.log("From title "+fromTitle+" from passed "+asDateFrom+" - From encoded "+fromEncoded);
    var url  = "/lsvc/reports-all-star/"+storeNumber+"/"+asRangeType+"/"+fromEncoded+"/"+toEncoded;

    var jqxhr = $.get( url, function(data) {
      showReport(data);
    })
    .fail(function(jqXHR) {
      $("#report-header").html("Invalid Request");
      $("#report-data").html("<tr><td><em>"+jqXHR.responseJSON.msg+".</em></td></tr>");
    })
    .always(function() {
      $("#allstar-run").show();
    });
}

function showReport(data){
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

          "<th class='text-right' ><strong>Sales W/O Hrs</strong></th>",
          "<th class='text-right' nowrap><strong>Returns W/O Hrs</strong></th>",

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
          secondaryHtml.push("<tr><td><strong>Returns:</strong></td><td class='text-right'> " + tr.ReturnWoHours + "</td></tr>");
          secondaryHtml.push("</tbody>");
      }

      tr.pDiffBud = parseCurrency(tr.DiffBud);
      tr.pDiffPct = parsePct((tr.Sales - tr.EmpTarget) / tr.EmpTarget);
      tr.pADS = parseCurrency(tr.ADS);
      tr.pEmpTarget = parseCurrency(tr.EmpTarget);
      tr.pHours = parseNum(tr.Hours);

      tr.pSales = parseCurrency(tr.Sales);
      tr.pSalesWoHours = parseCurrency(tr.SalesWoHours);
      tr.pReturnWoHours = parseCurrency(tr.ReturnWoHours);
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
              "<td class='text-right "+ ((tr.pReturnWoHours.isNegative) ? "text-danger" : "")+"'>"+tr.pReturnWoHours.parsed+"</td>",

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
  tots.pReturnWoHours = parseCurrency(tots.ReturnWoHours);
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
          "<td class='text-right "+ ((tots.pReturnWoHours.isNegative) ? "text-danger" : "")+"'>"+tots.pReturnWoHours.parsed+"</td>",
      "</tr>"
  );

  html.push("</tfoot>");

  $("#report-data").html(html.join(''));
  $("#report-data").tablesorter({
      sortList : [[0,0]],
  });

  $("#report-secondary").html(secondaryHtml.join(''));
}
