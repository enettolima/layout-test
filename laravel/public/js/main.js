/*
* -5817.55 -> ($5,817.55)
*  0.00 -> $0.00
*  7076.36 -> $7076.36
*/

function parseCurrency(x) {

	var retval = {};

	retval.input = x;
	retval.isNegative = false;
	retval.isNotAvailable = false;
	retval.parsed = 'n/a';

	x = parseFloat(x).toFixed(2);

	if (isNaN(x)) {
		retval.isNotAvailable = true;
		return retval;
	}

	if (x < 0) {
		x = Math.abs(x).toFixed(2);
		retval.isNegative = true;
	}

	var parts = x.toString().split(".");

	parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

	if (parts[1].length == 1) {
        parts[1] = parts[1] + "0";
	}

	var numWithCommas = parts.join(".");

	if (retval.isNegative) {
		retval.parsed =  "($" + numWithCommas + ")";
	} else {
		retval.parsed = "$" + numWithCommas;
	}

	return retval;
}

function parsePct(x)
{
    console.log(x);
		var retval = {};
		retval.input = x;
		retval.isNegative = false;
		retval.isNotAvailable = false;
		retval.parsed = 'n/a';

		x = parseFloat(x * 100).toFixed();

		if (isNaN(x)) {
				retval.isNotAvailable = true;
				return retval;
		}

        // Deal with Infinity the same as isNaN
        if (x == Number.POSITIVE_INFINITY || x == Number.NEGATIVE_INFINITY) {
				retval.isNotAvailable = true;
				return retval;
        }

		if (x < 0) {
				retval.isNegative = true;
		} 

		retval.parsed = x + "%";

		return retval;
}

function parseNum(x, places)
{
        places = typeof places !== 'undefined' ? places : 2;

		var retval = {};
		retval.input = x;
		retval.isNegative = false;
		retval.isNotAvailable = false;
		retval.parsed = 'n/a';

		x = parseFloat(x).toFixed(places);

		if (isNaN(x)) {
				retval.isNotAvailable = true;
				return retval;
		}

		if (x < 0) {
				retval.isNegative = true;
		} 

        var parts = x.toString().split(".");

        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

        if (parts.length > 1) {

            if (parts[1].length == 1) {
                parts[1] = parts[1] + "0";
            }

            retval.parsed = parts.join(".");
        } else {
            retval.parsed = parts[0];
        }

		return retval;
}

$(document).ready(function(){

    $(".users-table tr").click(function(){
        window.location.replace("/admin/user-edit/" + $(this).attr("data-userId"));
    });

    $("button.admin-users-add").on("click", function(){
        window.location.replace("/admin/user-edit/new");
    });

    $("a.change-store-context").on("click", function(){

        var 
            targetStore = $(this).attr('data-store-number'),
            currentStore = $("#current-store").html();

        if (targetStore != currentStore) {

            $("#current-store").html('<em>Loading...</em><img src="/images/ajax-loader-arrows.gif">');
            $("#current-store-name").html('');

            event.preventDefault();

            $.ajax({
                url: '/lsvc/check-store-auth',
                type: 'POST',
                data: {
                    'storeNumber' : $(this).attr('data-store-number')
                },
                complete: function (jqXHR, textStatus) {
                    // console.log('complete');
                },
                success: function (data, textStatus, jqXHR) {
                    location.reload();
                    // console.log(textStatus);
                    // console.log(data.status);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // console.log('error');
                }
            });
        }

        // console.log($(this).attr('data-foo'));
    });
});
