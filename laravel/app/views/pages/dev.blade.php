@extends('layouts.default')

@section('content')

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

        console.log(searchBody);

        var foo = {
            _source : true,
            index: "docs",
            body: searchBody
        };

        console.log(foo);

        client.search(foo).then(function (body) {
            var hits = body.hits.hits;
            console.log("Hits...");
            console.log(hits);
            for(var i=0; i< hits.length; i++) {
                var html = '';
                var fileloc = encodeURIComponent(hits[i]._source.filename);
                html += "<li><a href=\"/docs/" + fileloc + "\">" + hits[i]._source.filename + "</a><ul>";
                console.log(hits[i]._source.filename);
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

<h3>Document Library</h3>

<form class="search" method="GET">
    <input type="text" name="search" class="searchfield" placeholder="Enter Search Term">
    <input type="submit" val="Submit">
</form>

<h3>Results</h3>

<ul id="results">
</ul>

@stop
