@extends('layouts.default')

@section('content')

<h3>Documents</h3>

<script type="text/javascript" charset="utf-8">
$(function() {

    $("#spinny").hide();

    $("form.search").on("submit", function(event){

        $("#spinny").show();

        $("#resultsHeader").hide();

        event.preventDefault();


        $("#results").empty();


        var data = {
            query: {
                match: {
                    _all: $('.searchfield').val()
                }
            },
            fields : ["filename", "url"],
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

                        console.log(data[i]);

                        source = data[i].fields;

                        var url = source["file.url"][0];

                        var re = /^file:\/\/\/media\/web\/downloads\/(.*)$/;

                        if (url) {

                            var fixed = url.match(re)[1];

                            var full = "/docs/" + fixed;

                            var row = '';

                            $("#results").append("<li><a target=\"_blank\" href=\""+full+"\">"+full+"</a></li>");
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

    });
});

</script>

<form class="search" method="GET">
    <input type="text" name="search" class="searchfield" placeholder="Enter Search Term">
    <input type="submit" val="Submit">&nbsp;<span style="" id="spinny"><img src="/images/ajax-loader-arrows.gif"></span>
</form>

<h3 id="resultsHeader">Results</h3>

<style type="text/css">
#results em {
    background-color:#FCF8E6;
    font-weight:bold;
}
</style>

<ul id="results">
</ul>

@stop
