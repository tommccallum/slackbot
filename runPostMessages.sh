#!/bin/bash

# Adds a file lock around the post messages script so it does not get run multiple times.

PHP=$(which php)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
SCRIPT="$SCRIPT_DIR/responder/postMessages.php"
LOCKFILE="/tmp/slackbot_postmessages"

# check if the PID in the lockfile is still running
# if not then we delete
if [ -e "$LOCKFILE" ]; then
    read lastPID <"$LOCKFILE"
    [ ! -z "$lastPID" -a -d /proc/$lastPID ] && exit
    echo "$(date) Lock file ${LOCKFILE} is still in place but associated process is dead, removing lockfile."
    lockfile-remove "$LOCKFILE"
fi

lockfile-create --use-pid -r 0 "$LOCKFILE"
if [ $? -ne 0 ]; then
    echo "$(date) Lock file ${LOCKFILE} is in place, exiting without action."
    exit 0
fi

$PHP "$SCRIPT" "$@"

lockfile-remove "$LOCKFILE"
