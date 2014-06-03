#!/bin/bash

curl -X DELETE "localhost:9200/docs"

curl -X PUT "localhost:9200/docs" -d '{
  "settings" : { "index" : { "number_of_shards" : 1, "number_of_replicas" : 0 }}
}'

curl -X PUT "localhost:9200/docs/attachment/_mapping" -d '{
  "attachment" : {
    "properties" : {
      "file" : {
        "type" : "attachment",
        "fields" : {
          "title" : { "store" : "yes" },
          "file" : { "term_vector":"with_positions_offsets", "store":"yes" }
        }
      }
    }
  }
}'
