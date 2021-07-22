#!/bin/bash

# Adds a file lock around the post messages script so it does not get run multiple times.

PHP=$(which php)
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
SCRIPT="$SCRIPT_DIR/responder/replyToMessages.php"
LOCKFILE="/tmp/slackbot_replytomessages"

lockfile-create -r 0 "$LOCKFILE"
if [ $? -ne 0 ]; then
    echo "Lock file ${LOCKFILE} is in place, existing without action."
    exit 1
fi

$PHP "$SCRIPT"

lockfile-remove $LOCKFILE
