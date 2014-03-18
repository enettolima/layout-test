$(document).ready(function(){

    $("a.change-store-context").on("click", function(){

        $("#current-store").html('<em>Loading...</em><img src="/images/ajax-loader-arrows.gif">');

        console.log($(this).attr('data-store-number'));

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

        // console.log($(this).attr('data-foo'));
    });
});
