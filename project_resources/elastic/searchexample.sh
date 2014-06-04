curl "localhost:9200/_search?pretty=true" -d '{
    "fields" : ["title"],
    "query" : {
    "query_string" : {
    "query" : "amplifier"
    }
    },
    "highlight" : {
        "fields" : {
    "file" : {}
        }
    }
}'
