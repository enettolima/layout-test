@extends('layouts.default')

@section('content')

<h3>Documents</h3>

<script type="text/javascript" charset="utf-8">
$(function() {

    $("#spinny").hide();

    var client = new $.es.Client({
        hosts: 'dev.ebtpassport.com:9200'
    });

    $("form.search").on("submit", function(event){

        $("#spinny").show();

        event.preventDefault();

        $("#results").empty();

        var query = $(".searchfield").val();

        var search = {
            "_source" : true, 
            "index": "docs",
            "body": {
                "fields" : [],
                "query" : {
                    "query_string" : {
                        "query" : query
                    }
                },
                "highlight" : {
                    "fields" : {
                        "file" : {}
                    }
                }
            }
        };

        client.search(search).then(function (body) {

            $("#spinny").hide();

            var hits = body.hits.hits;

            if (hits.length) {
                for(var i=0; i< hits.length; i++) {
                    var html = '';
                    var fileloc = encodeURIComponent(hits[i]._source.filename);
                    html += "<li><a href=\"/docs/" + fileloc + "\">" + hits[i]._source.filename + "</a><ul>";
                    /*console.log(hits[i]._source.filename);*/
                    console.log("here comes highlight");
                    console.log(hits[i]);
                    /*console.log(hits[i].highlight);*/
                    /*console.log(hits[i].highlight.file);*/
                    var highlights = hits[i].highlight.file;
                    for (var h=0; h < highlights.length; h++) {
                        console.log(highlights[h]);
                        html += "<li>" + highlights[h] + "</li>";
                    }
                    html += "</ul></li>";
                    $("#results").append(html);
                }
            } else {
                $("#results").append("<li>No results found.</li>");
            }
        }, function (error) {
            console.trace(error.message);
        });

    });

});
</script>

<form class="search" method="GET">
    <input type="text" name="search" class="searchfield" placeholder="Enter Search Term">
    <input type="submit" val="Submit">&nbsp;<span style="" id="spinny"><img src="/images/ajax-loader-arrows.gif"></span>
</form>

<h3>Results</h3>

<style type="text/css">
#results em {
    background-color:#FCF8E6;
    font-weight:bold;
}
</style>

<ul id="results">
</ul>

@stop
