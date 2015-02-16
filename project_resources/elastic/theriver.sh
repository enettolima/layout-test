#!/bin/bash

svc="ebtpassport.com:9200"

curl -XDELETE ${svc}/mydocs
curl -XDELETE ${svc}/_river

curl -XPUT ${svc}/mydocs/ -d '{}'
curl -XPUT ${svc}/_river/mydocs/_meta -d '
{
    "type" : "fs",
    "fs" : {
        "url" : "/media/web/downloads",
        "update_rate":120000,
        "includes":"*.pdf,*.doc,*.txt"
    }
}
'
