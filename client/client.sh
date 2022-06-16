#!/bin/bash

# HOW TO RUN:

# Have the server running first:
# $ php -S 127.0.0.1:8000 -t public

# List all users:
# $ ./client.sh getallusers
# $ ./client.sh getuser
# adapt the code with jq (https://stedolan.github.io/jq/) if you need pretty-printing

# List a user ID:
# $ ./client.sh getuser 2
# adapt the code with jq (https://stedolan.github.io/jq/) if you need pretty-printing

# Add a user:
# $ ./client.sh adduser '{"firstname": "Dennis", "lastname": "Ritchie", "firstparent_id": 4, "secondparent_id": 3}'

# Update a user by ID:
# $ ./client.sh updateuser 90 '{"firstname": "Dennis", "lastname": "Ritchie", "firstparent_id": 5, "secondparent_id": 6}'

# Delete a user by ID:
# $ ./client.sh deleteuser 7

ENV="$1"
[ ! -f "$ENV" ] && ENV= || shift

get_token() {
    curr_dir="$(pwd)"
    dir="$0"
    full_path="$curr_dir/$dir"
    full_dir="$(dirname "$full_path")"
    get_token="${full_dir}/get_token.sh"
    token="$(bash $get_token "$ENV")"
    return
}

create_headers() {
    for ((i=0; i<${#headers[@]}; i++)); do
        headers_str+=" -H '${headers[$i]}'"
    done
    headers_str=${headers_str:1}
}

COMMAND=$1
shift
ID="$1"
token=
get_token

# use the code below for the --oauth2-bearer option in curl
# otherwise comment this and use the token directly as the "Authorization" header
token=$(echo $token | sed 's/Bearer //')

headers=()
headers+=("Content-Type: application/json")

# use the code below if you won't use the --oauth2-bearer option in curl
#headers+=("Authorization: $token")

headers_str=''
create_headers

URL="http://127.0.0.1:8000/person"

if [[ "$COMMAND" == "getallusers" || "$COMMAND" == "getuser" ]]; then
    [ "$COMMAND" == "getuser" ] && URL+="/$ID"
    curlcmd="curl -X GET --oauth2-bearer '$token' $URL"
    eval "$curlcmd"
    echo ""
elif [ "$COMMAND" == "adduser" ]; then
    input="$1"
    tempfile=$(mktemp)
    # remove the --oauth2-bearer option if it was added in the headers string above
    curlcmd="curl -X POST $headers_str --oauth2-bearer '$token' -d '$input' -D $tempfile  $URL"
    eval "$curlcmd"
    echo ""
    status_code=$(head -1 $tempfile | cut -f2- -d' ' | sed 's/\r//')
    [ "$status_code" != "401 Unauthorized" -a "$status_code" != "404 Not Found" ] && echo "$status_code"
    rm $tempfile
elif [ "$COMMAND" == "updateuser" ]; then
    URL+="/$ID"
    shift
    input="$1"
    tempfile=$(mktemp)
    # remove the --oauth2-bearer option if it was added in the headers string above
    curlcmd="curl -X PUT $headers_str --oauth2-bearer '$token' -d '$input' -D $tempfile $URL"
    eval "$curlcmd"
    echo ""
    status_code=$(head -1 $tempfile | cut -f2- -d' ' | sed 's/\r//')
    [ "$status_code" != "401 Unauthorized" -a "$status_code" != "404 Not Found" ] && echo "$status_code"
    rm $tempfile
elif [ "$COMMAND" == "deleteuser" ]; then
    URL+="/$ID"
    tempfile=$(mktemp)
    curlcmd="curl -X DELETE --oauth2-bearer '$token' -D $tempfile $URL"
    eval "$curlcmd"
    status_code=$(head -1 $tempfile | cut -f2- -d' ' | sed 's/\r//')
    [ "$status_code" != "401 Unauthorized" -a "$status_code" != "404 Not Found" ] && echo "$status_code"
    rm $tempfile
    echo ""
else
    echo "Command not found"
    echo 'Command = getallusers|getuser|adduser|updateuser|deleteuser'
fi
