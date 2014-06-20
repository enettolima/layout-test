#!/bin/bash

curl -XGET dev.ebtpassport.com:9200/mydocs/doc/_search -d '
{
    "query" : {
        "match" : {
            "_all" : "voice"
        }
    },
    "fields" : ["filename"]
}
'
