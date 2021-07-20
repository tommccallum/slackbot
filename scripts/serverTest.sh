#!/bin/bash

HOOK="https://asd.uhi.ac.uk/slackbot/check.php"
curl -X POST -H 'Content-type: application/json' --data '{"text":"Hello, World!"}' "$HOOK"
