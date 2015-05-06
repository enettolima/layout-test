#!/bin/bash

#svc="ebtpassport.com:9200"
svc="192.168.2.155:9200"

printf "Deleting previous indexes\n"
curl -XDELETE ${svc}/mydocs
curl -XDELETE ${svc}/eb_documents
curl -XDELETE ${svc}/_river

printf "\n\nCreating eb_documents configuration\n"
curl -XPUT ${svc}/eb_documents/ -d '{
  "settings": {
    "index": {
      "number_of_replicas": "0",
      "number_of_shards": "5"
    }
  },
  "mappings": {
    "doc": {
      "properties": {
        "content": {
          "store": true,
          "type": "string"
        },
        "file": {
          "properties": {
            "last_modified": {
              "store": true,
              "format": "dateOptionalTime",
              "type": "date"
            },
            "filesize": {
              "store": true,
              "type": "long"
            },
            "indexed_chars": {
              "store": true,
              "type": "long"
            },
            "indexing_date": {
              "store": true,
              "format": "dateOptionalTime",
              "type": "date"
            },
            "filename": {
              "index": "not_analyzed",
              "store": true,
              "type": "string"
            },
            "content_type": {
              "index": "not_analyzed",
              "store": true,
              "type": "string"
            },
            "url": {
              "index": "no",
              "store": true,
              "type": "string"
            }
          }
        },
        "path": {
          "properties": {
            "virtual": {
              "index": "not_analyzed",
              "store": true,
              "type": "string"
            },
            "real": {
              "index": "not_analyzed",
              "store": true,
              "type": "string"
            },
            "root": {
              "index": "not_analyzed",
              "store": true,
              "type": "string"
            },
            "encoded": {
              "index": "not_analyzed",
              "store": true,
              "type": "string"
            }
          }
        },
        "meta": {
          "properties": {
            "author": {
              "store": true,
              "type": "string"
            },
            "title": {
              "store": true,
              "type": "string"
            },
            "keywords": {
              "store": true,
              "type": "string"
            },
            "date": {
              "store": true,
              "format": "dateOptionalTime",
              "type": "date"
            }
          }
        }
      }
    },
    "folder": {
      "properties": {
        "virtual": {
          "index": "not_analyzed",
          "type": "string"
        },
        "root": {
          "type": "string"
        },
        "name": {
          "type": "string"
        },
        "encoded": {
          "type": "string"
        }
      }
    }
  }
}
'
printf "\n\nCreating eb_documents river\n"
curl -XPUT ${svc}/_river/eb_documents/_meta -d '
{
    "type" : "fs",
    "fs" : {
        "url" : "/media/web/downloads",
        "update_rate":120000,
        "includes":"*.pdf,*.doc,*.txt"
    }
}
'

printf "\n\nCreating eb_documents alias --> dir\n"
curl -XPOST  ${svc}/_aliases -d '
{
    "actions" : [
        { "add" : { "index" : "eb_documents", "alias" : "dir" } }
    ]
}'

#to remove
#printf "\n\nCreating mydocs river\n"
#printf "\n***** MAKE SURE TO REMOVE THIS PART OF THE SCRIPT\n"
#printf "WHEN NEW VERSION OF THE DOC SEARCH IS ON PRODUCTION *****\n"
#curl -XPUT ${svc}/mydocs/ -d '{}'
#curl -XPUT ${svc}/_river/mydocs/_meta -d '
#{
#    "type" : "fs",
#    "fs" : {
#        "url" : "/media/web/downloads",
#        "update_rate":120000,
#        "includes":"*.pdf,*.doc,*.txt"
#    }
#}
#'
#remove until here

printf "\n\nDone configuring elasticsearch, please check your head plugin\n"
printf "to make sure eb_documents was created and the alias dir is there\n"
