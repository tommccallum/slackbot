#!/bin/bash

if [ ! -e "dist" ]; then
    mkdir dist
fi

cp -R src/* dist
if [ -e ".htenv.php" ]; then
    echo "Copying local copy of .htenv.php"
    cp .htenv.php dist
fi

if [ -e "users.json" ]; then
    echo "Copying over users.json file to .htdata directory"
    mkdir dist/.htdata
    cp users.json dist/.htdata
fi

echo "Deployable files are in dist, you just need to copy them to /var/www/html on the server"