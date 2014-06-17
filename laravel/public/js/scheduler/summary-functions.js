/*
 * This class is meant to be fed goals & inOuts for 
 * one day and the functions will return the appropriate
 * summaries. 
 */

function SchedulerSummary(goals, inOuts){

    /*
        goals format:
        [
            {"hour" : 10, "goal" : 100.00},
            {"hour" : 11, "goal" : 110.00},
            {"hour" : 12, "goal" : 120.00},
            {"hour" : 13, "goal" : 130.00},
            {"hour" : 14, "goal" : 140.00},
            {"hour" : 15, "goal" : 150.00},
            {"hour" : 16, "goal" : 160.00},
            {"hour" : 17, "goal" : 170.00},
            {"hour" : 18, "goal" : 180.00},
            {"hour" : 19, "goal" : 190.00},
            {"hour" : 20, "goal" : 200.00}
        ];
    */
    if (typeof goals === 'undefined') {
        throw "Error: goals is undefined.";
    } else {
        this.goals = goals;
    }

    /*
        inOuts format:
        [ 
            {
                "associate_id": "000AA",
                "date_in" : "2014-06-08 11:45:00",
                "date_out" : "2014-06-08 12:30:00",
                "store_id" : 444
            },
            {
                "associate_id": "000AA",
                "date_in" : "2014-06-08 13:00:00",
                "date_out" : "2014-06-08 14:00:00",
                "store_id" : 444
            }
        ];
    */

    if (typeof inOuts === 'undefined') {
        throw "Error: inOuts is undefined.";
    } else { 
        this.inOuts = inOuts;
    }

    /*
     * Create array of minutes which employees are working
     * and the number of employees per that minute to refer
     * to later.
     */
    this.mins = [];

    for (var d=0; d<this.inOuts.length; d++) {

        var start = this.dateTimeToMin(this.inOuts[d].date_in);

        var end = this.dateTimeToMin(this.inOuts[d].date_out);

        for (var onMin = start; onMin < end; onMin++) {

            if (typeof this.mins[onMin] === "undefined") {
                this.mins[onMin] = 0;
            }

            this.mins[onMin]++;
        }

    }
}

SchedulerSummary.prototype.dateTimeToMin = function(dateString) {
    var returnval = false;

    var re = /^\d{4}-\d{2}-\d{2}\s(\d{2}):(\d{2}):(\d{2})$/;

    var matches = re.exec(dateString);

    if (matches) {
        returnval = (parseInt(matches[1]) * 60) + parseInt(matches[2]);
    } 

    return returnval;
};

SchedulerSummary.prototype.getBudgetByEmployee = function() {

    var returnval = [];

    var hourLookup = [];

    var bbh = this.getBudgetByHour();

    for (var h=0; h<bbh.length; h++) {
        hourLookup[bbh[h].hour] = bbh[h];
    } 

    for (var io=0; io < this.inOuts.length; io++) {

        if (typeof returnval[this.inOuts[io].associate_id] === 'undefined') {
            returnval[this.inOuts[io].associate_id] = 0;
        }

        var minFrom = this.dateTimeToMin(this.inOuts[io].date_in);

        var minTo = this.dateTimeToMin(this.inOuts[io].date_out);

        for (var m = minFrom; m < minTo; m++) {

            // What hour does this m belong to?
            var mHour = parseInt (m / 60);

            // If this is a valid hour to be working in... won't this fail
            // for half-hours?
            if (typeof hourLookup[mHour] !== "undefined") {
                returnval[this.inOuts[io].associate_id] += hourLookup[mHour].goal / hourLookup[mHour].empMin;
            }
        }
    }

    return returnval;
};

SchedulerSummary.prototype.getBudgetByHour = function() {

    var returnval = [];

    // Create a lookup of the empMins  

    for (var g=0; g<this.goals.length; g++) {

        var empMin = 0;

        var minsFrom = (this.goals[g].hour * 60);

        var minsTo = minsFrom + 59;

        for (atMin = minsFrom; atMin <= minsTo; atMin++) {
            if (typeof this.mins[atMin] !== "undefined") {
                empMin = empMin + this.mins[atMin];
            }
        }

        var budget = this.goals[g].goal / empMin;

        returnval.push({
            "hour": this.goals[g].hour, 
            "goal" : this.goals[g].goal,
            "minsFrom" : minsFrom,
            "minsTo" : minsTo,
            "empMin" : empMin,
            "budget" : budget
        });
    }

    return returnval;
};
