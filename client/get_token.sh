#!/bin/bash

ENV="$1"
[ -z "$ENV" ] && ENV=".env"
[ ! -f "$ENV" ] && {
    echo "Environmental file not found"
    exit 1
}

scope=$(grep -Po "(?<=SCOPE=).*" "$ENV")
clientId=$(grep -Po "(?<=OKTACLIENTID=).*" "$ENV")
clientSecret=$(grep -Po "(?<=OKTASECRET=).*" "$ENV")

uri="$(grep -Po "(?<=OKTAISSUER=).*" "$ENV")/v1/token"
payload="grant_type=client_credentials&scope=$scope"
token=$(base64 -w0 < <(echo -n "$clientId:$clientSecret"))
contentType="Content-Type: application/x-www-form-urlencoded"
authorization="Authorization: Basic $token"

curlcmd="curl -X POST -d '$payload' -H '$contentType' -H '$authorization' $uri"
output="$(eval $curlcmd 2>/dev/null)"
output="$(echo "$output" | sed 's/{/(/')"
token_type="$(echo "$output" | grep -Po "(?<=token_type\":\")[^\"]+")"
access_token="$(echo "$output" | grep -Po "(?<=access_token\":\")[^\"]+")"
echo "$token_type $access_token"
