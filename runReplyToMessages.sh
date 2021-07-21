#!/bin/bash

# Adds a file lock around the post messages script so it does not get run multiple times.

PHP=$(which php)
SCRIPT="$HOME/slackbot/responder/postMessages.php"
LOCKFILE="/tmp/slackbot_postmessages.lock"

lockfile -r 0 "$LOCKFILE"
if [ $? -ne 0 ]; then
    echo "Lock file ${LOCKFILE} is in place, existing without action."
    exit 1
fi

$PHP "$SCRIPT"

rm -f $LOCKFILE
