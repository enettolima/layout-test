#!/bin/bash

coded=`cat "$1" | perl -MMIME::Base64 -ne 'print encode_base64($_)'`
json="{\"file\":\"${coded}\"}"
echo "$json" > json.file
curl -X POST "localhost:9200/docs/attachment/" -d @json.file
echo ""
