$(document).on("click", ".remove-override", function(e){

    e.preventDefault();

    var overrideId  = $(this).attr("data-override-id");
    var parentRow   = $(this).closest("tr");
    var dateString  = parentRow.find(".override-date").html().trim();
    var openString  = parentRow.find(".override-open").html().trim();
    var closeString = parentRow.find(".override-close").html().trim();

    var html = [];

    html.push("<p>Are you sure you want to delete the following override?</p>");
    html.push("<ul>");
        html.push("<li>");
            html.push("<strong>Date:</strong> " + dateString);
        html.push("</li>");
        html.push("<li>");
            html.push("<strong>Open:</strong> " + openString);
        html.push("</li>");
        html.push("<li>");
            html.push("<strong>Close:</strong> " + closeString);
        html.push("</li>");
    html.push("</ul>");

    $("#remove-override-modal-content").html(html.join(""));

    $("#remove-override-modal-confirm").attr("href",  '/scheduler/override-hours-delete/' + overrideId);

});

$(document).on("click", "#remove-override-modal-confirm", function(e){

    var overrideId = $(this).attr("data-override-id");

});
