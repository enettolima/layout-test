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

    var reportRangeControl = $("#allstar-report-range");
    reportRangeControl.val(asRangeType);

    var cookieMoment = new moment().add(1, 'day');

    $.cookie('asRangeType', asRangeType, {expires: cookieMoment.toDate()});

    $.cookie('asRangeVal', asRangeVal, {expires: cookieMoment.toDate()});

    switch(asRangeType) {
        case 'month':
            $("#allstar-month").val(asRangeVal);
            $("#allstar-options-month").show();
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
