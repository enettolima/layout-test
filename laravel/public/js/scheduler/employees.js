/* Load up employees from source */
/* TODO: Cache this? We Don't want to perform a Get every time, but I'm not sure how to cache JS stuff */
/* How about: http://stackoverflow.com/questions/17104265/caching-a-jquery-ajax-response-in-javascript-browser */
/* Or: http://stackoverflow.com/questions/20667527/jquery-ajax-cache-response */
/* TODO: How do we know if this fails? What do we do when it fails? */

var employeesFromService;

var employeeRequest = $.ajax({
    url: "/lsvc/employees",
    type: "GET",
    async: false,
    cache: true,
    global: false
}).done(function(msg){
    employeesFromService = msg;
});

function getEmpNameFromCode(strCode, empMasterDatabase){
    var results = $.grep(empMasterDatabase, function(e){ return e.userId === strCode; });

    if (results.length === 0) {
        return '';
    } else if (results.length === 1) {
        return results[0].fullName;
    } else {
        return '';
    }
}

function getEmpIsManager(strCode, empMasterDatabase) {
    var results = $.grep(empMasterDatabase, function(e){ return e.userId === strCode; });

    if (results.length === 0) {
        return false;
    } else if (results.length === 1) {
		if (results[0].manager == 'M' || results[0].manager == 'm') {
			return true;
		}
    } else {
        return false;
    }
}
