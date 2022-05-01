#!/bin/bash

# HOW TO RUN:

# Have the server running first:
# $ php -S 127.0.0.1:8000 -t public

# List all users:
# $ ./client.sh getallusers
# adapt the code with jq (https://stedolan.github.io/jq/) if you need pretty-printing

# List a user ID:
# $ ./client.sh getuser 2
# adapt the code with jq (https://stedolan.github.io/jq/) if you need pretty-printing

# Add a user:
# $ ./client.sh adduser '{"firstname": "Dennis", "lastname": "Ritchie", "firstparent_id": 4, "secondparent_id": 3}'

# Update a user by ID:
# $ ./client.sh updateuser 90 '{"firstname": "Dennis", "lastname": "Ritchie", "firstparent_id": 5, "secondparent_id": 6}

# Delete a user by ID:
# $ ./client.sh deleteuser 89

COMMAND=$1
shift
ID="$1"

URL="http://127.0.0.1:8000/person"

if [[ "$COMMAND" == "getallusers" || "$COMMAND" == "getuser" ]]; then
    [ "$COMMAND" == "getuser" ] && URL+="/$ID"
    curl -X GET "$URL"
    echo ""
elif [ "$COMMAND" == "adduser" ]; then
    header="Content-Type: application/json"
    input="$1"
    curlcmd="curl -X POST -d '$input' -H '$header' $URL"
    eval "$curlcmd"
    echo ""
elif [ "$COMMAND" == "updateuser" ]; then
    header="Content-Type: application/json"
    URL+="/$ID"
    shift
    input="$1"
    curlcmd="curl -X PUT -d '$input' -H '$header' $URL"
    eval "$curlcmd"
    echo ""
elif [ "$COMMAND" == "deleteuser" ]; then
    URL+="/$ID"
    curl -X DELETE "$URL"
    echo ""
else
    echo "Command not found"
    echo 'command = getallusers|getuser|adduser|updateuser|deleteuser'
fi
