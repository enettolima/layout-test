@extends('layouts.default')

@section('content')

<h3>Documents</h3>

<script type="text/javascript" charset="utf-8">
$(function() {

    var client = new $.es.Client({
        hosts: 'dev.ebtpassport.com:9200'
    });

    $("form.search").on("submit", function(event){
        event.preventDefault();

        $("#results").empty();

        var query = $(".searchfield").val();

        var searchBody = {
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
        };

        var foo = {
            "_source" : true, 
            "index": "docs",
            "body": searchBody
        };

        client.search(foo).then(function (body) {
            var hits = body.hits.hits;
            for(var i=0; i< hits.length; i++) {
                var html = '';
                console.log(hits[i]);
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
        }, function (error) {
            console.trace(error.message);
        });

    });

});
</script>

<form class="search" method="GET">
    <input type="text" name="search" class="searchfield" placeholder="Enter Search Term">
    <input type="submit" val="Submit">
</form>

<h3>Results</h3>

<ul id="results">
</ul>

@stop
