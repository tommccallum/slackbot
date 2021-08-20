#!/bin/bash

# Adds a file lock around the post messages script so it does not get run multiple times.

PHP=$(which php)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
SCRIPT="$SCRIPT_DIR/responder/replyToMessages.php"
LOCKFILE="/tmp/slackbot_replytomessages"

# check if the PID in the lockfile is still running
# if not then we delete
if [ -e "$LOCKFILE" ]; then
    read lastPID <"$LOCKFILE"
    [ ! -z "$lastPID" -a -d /proc/$lastPID ] && exit
    lockfile-remove "$LOCKFILE"
fi

lockfile-create --use-pid -r 0 "$LOCKFILE"
if [ $? -ne 0 ]; then
    echo "Lock file ${LOCKFILE} is in place, existing without action."
    exit 1
fi

$PHP "$SCRIPT" --loop --quiet "$@" &
