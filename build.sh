#!/bin/bash

composer update
if [ $? -ne 0 ]; then 
    echo "Composer failed, please fix and then try again."
    exit 1
fi

if [ ! -e "dist" ]; then
    mkdir dist
fi

cp -R common/* dist
cp -R webclient/* dist
cp -R vendor dist
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