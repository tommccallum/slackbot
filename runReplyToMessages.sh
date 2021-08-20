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
    echo "$(date) Lock file ${LOCKFILE} is in place but application is not running, removing lockfile and continuing."
    lockfile-remove "$LOCKFILE"
fi

# hide the lockfile creation message
lockfile-create --use-pid -r 0 "$LOCKFILE" >/dev/null 2>&1
if [ $? -ne 0 ]; then
    #echo "Lock file ${LOCKFILE} is in place, exiting without action."
    exit 0
fi

$PHP "$SCRIPT" --loop --quiet "$@"
