#!/bin/bash

if [ ! -e "dist" ]; then
    mkdir dist
fi

cp -R src/* dist
if [ -e ".htenv.php" ]; then
    cp .htenv.php dist
fi

echo "Deployable files are in dist, you just need to copy them to /var/www/html on the server"