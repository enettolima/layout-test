var data = [ 
    {
        "associate_id": "000AA",
        "date_in" : "2014-06-08 09:00:00",
        "date_out" : "2014-06-08 10:00:00",
        "store_id" : 444
    },
    {
        "associate_id": "000AA",
        "date_in" : "2014-06-08 09:00:00",
        "date_out" : "2014-06-08 10:00:00",
        "store_id" : 444
    },
    /*
    {
        "associate_id": "000AA",
        "date_in" : "2014-06-08 13:00:00",
        "date_out" : "2014-06-08 14:00:00",
        "store_id" : 444
    }
   */
];

var goals = [
    {"hour" : 0, "goal" : 100.00},
    {"hour" : 1, "goal" : 100.00},
    {"hour" : 2, "goal" : 100.00},
    {"hour" : 3, "goal" : 100.00},
    {"hour" : 4, "goal" : 100.00},
    {"hour" : 5, "goal" : 100.00},
    {"hour" : 6, "goal" : 100.00},
    {"hour" : 7, "goal" : 100.00},
    {"hour" : 8, "goal" : 100.00},
    {"hour" : 9, "goal" : 200.00},
    {"hour" : 10, "goal" : 100.00},
    {"hour" : 11, "goal" : 100.00},
    {"hour" : 12, "goal" : 100.00},
    {"hour" : 13, "goal" : 100.00},
    {"hour" : 14, "goal" : 100.00},
    {"hour" : 15, "goal" : 100.00},
    {"hour" : 16, "goal" : 100.00},
    {"hour" : 17, "goal" : 100.00},
    {"hour" : 18, "goal" : 100.00},
    {"hour" : 19, "goal" : 100.00},
    {"hour" : 20, "goal" : 100.00}
];

var mins = [];

function timeToMin(dateString) {

    var returnval = false;

    var re = /^\d{4}-\d{2}-\d{2}\s(\d{2}):(\d{2}):(\d{2})$/;

    var matches = re.exec(dateString);

    if (matches) {
        returnval = (parseInt(matches[1]) * 60) + parseInt(matches[2]);
    } 

    return returnval;
}


for (var d=0; d<data.length; d++) {
    
    var start = timeToMin(data[d].date_in);

    var end = timeToMin(data[d].date_out);

    for (var onMin = start; onMin <= end; onMin++) {

        if (typeof mins[onMin] === "undefined") {
            mins[onMin] = 0;
        }

        mins[onMin]++;
    }

}

for (var g=0; g<goals.length; g++) {

    // Figure out the range for this hour... 
    
    var empMin = 0;

    var minsFrom = (goals[g].hour * 60);

    var minsTo = minsFrom + 59;

    for (atMin = minsFrom; atMin <= minsTo; atMin++) {
        if (typeof mins[atMin] !== "undefined") {
            empMin = empMin + mins[atMin];
        }
    }

    var budget = goals[g].goal / empMin;

    console.log({
        "hour": goals[g].hour, 
        "goal" : goals[g].goal,
        "minsFrom" : minsFrom,
        "minsTo" : minsTo,
        "empMin" : empMin,
        "budget" : budget
    });
}
