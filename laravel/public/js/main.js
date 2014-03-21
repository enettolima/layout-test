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
