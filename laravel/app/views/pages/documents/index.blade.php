@extends('layouts.default')

@section('content')

<script type="text/javascript" charset="utf-8">

function doSearch(searchstring) {
        $("#spinny").show();

        $("#resultsHeader").hide();

        event.preventDefault();

        $("#results").empty();

        var data = {
            query: {
                match: {
                    _all: searchstring
                }
            },
            fields : ["filename", "url", "path.virtual", "lastdate", "date"],
            size : 10000,
            highlight : {
                "fields" : {
                    "content" : {}
                }
            }
            // fields: '_id'
        }; 

        $.ajax({
            url: 'http://dev.ebtpassport.com:9200/mydocs/doc/_search',
            type: 'POST',
            //contentType: 'application/json; charset=UTF-8',
            crossDomain: true,
            dataType: 'json',
            data: JSON.stringify(data),
            success: function(response) {
                $("#spinny").hide();
                var data = response.hits.hits;
                var doc_ids = [];
                var source = null;
                var content = '';

                if (data.length > 0) {
                    $("#resultsHeader").html(data.length + " Results").show();
                    for (var i = 0; i < data.length; i++) {

                        source = data[i].fields;

                        var url = source["file.url"][0];

                        var dateString = undefined;

                        if (typeof source["meta.date"] !== "undefined") {

                            var formattedDate = new Date(source["meta.date"][0]);

                            var d = formattedDate.getDate();
                            var m =  formattedDate.getMonth();
                            m += 1;  // JavaScript months are 0-11
                            var y = formattedDate.getFullYear();

                            //dateString = source["meta.date"][0];
                            dateString = m + "/" + d + "/" + y;
                        }

                        var re = /^file:\/\/\/media\/web\/downloads\/(.*)$/;

                        if (url) {

                            var highlight = "Summary Not Available";

                            if (data[i].highlight) {

                                highlight = data[i].highlight.content[0];

                             }

                            var fixed = url.match(re)[1];

                            var filename = source["file.filename"][0];

                            var full = "/docs" + source["path.virtual"][0] + encodeURIComponent(filename);

                            var row = "";

                            row += "<li>";
                            row += "<h4><a target='_blank' href='"+full+"'>"+filename+"</a></h4>";
                            row += "<ul>";
                            if (typeof dateString !== "undefined") {
                                row += "<li><strong>File Date:</strong> "+dateString+"</li>";
                            }
                            row += "<li>"+highlight+"</li>";
                            row += "</ul>";
                            row += "</li>";

                            $("#results").append(row); 
                        }

                    }

                } else {
                    $("#resultsHeader").html("No Results").show();
                }

            },
            error: function(jqXHR, textStatus, errorThrown) {
                var jso = jQuery.parseJSON(jqXHR.responseText);
                error_note('section', 'error', '(' + jqXHR.status + ') ' + errorThrown + ' --<br />' + jso.error);
            }
        });
}

$(document).ready(function(){

    if ($('.searchfield').val()) {
        doSearch($('.searchfield').val());
    }

    $("form").on("submit", function(event){
        doSearch($('.searchfield').val());
    });
});

</script>

<div class="doc-search">

<h3>Earthbound Documents</h3>

<form role="form" method="GET">
  <div class="form-group">
      <input value="<?php echo Input::get('search'); ?>" type="text" class="form-control searchfield" name="search" name="search" placeholder="Search Documents" autofocus>
  </div>
  <input val="Search" type="submit" class="btn btn-default">&nbsp;<span style="display:none;" id="spinny"><img src="/images/ajax-loader-arrows.gif"></span>
</form>

<ul id="results"></ul>

</div>

@stop
