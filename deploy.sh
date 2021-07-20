#!/bin/bash

DEPLOYDIR="/var/www/html/slackbot"
LOGFILE="${DEPLOYDIR}/logs/slack.log"

echo
echo "Deploying Slackbot to server"
echo "----------------------------"
echo
echo "Assumes we are running from the repository directory."
echo "Targeting directory: $DEPLOYDIR"
echo "Using log file:      $LOGFILE"
echo
echo "If anything goes wrong check these are correct and update the file with any changes."
echo

if [ ! -e "$DEPLOYDIR" ]; then
    echo "[ERROR] Deployment directory does not exist, please create this first."
    exit 1
fi

echo "Pulling down latest code from github"
git pull
if [ $? -ne 0 ]; then
    echo "[ERROR] Failed to update repository, check and try again."
    exit 1
fi

echo "Building distribution"
./build.sh
if [ $? -ne 0 ]; then
    echo "[ERROR] Failed to build successfully, check and try again."
    exit 1
fi

echo "Truncating log file"
[[ -e "$LOGFILE" ]] && rm $LOGFILE

echo "Copying distribution files to live location"
cp -R dist/* "$DEPLOYDIR"
if [ $? -ne 0 ]; then
    echo "[ERROR] Failed to copy over new files to deployment directory."
    exit 1
fi


echo "Update is successfully deployed."

touch "$LOGFILE"
tail -f "${LOGFILE}"
