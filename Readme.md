# Slackbot

Slackbot is a Virtual Assistant that has been designed to deliver a Digital Narrative to one or more registered members.

## Dependencies

Ensure you are using PHP 7.2 or above.

We are using mongodb database to store all the JSON information we use.  

```
sudo apt install mongodb
sudo apt install php-pear
sudo apt install php-dev
sudo pecl install mongodb
```

## Getting started

Go to where you want to download the Slackbot to, this should not be an area accessible from the internet.

```
git clone https://github.com/tommccallum/slackbot
cd slackbot
./build.sh
cp -R dist/* /var/www/html/slackbot
```

## Securing on your server

* Add .htaccess to logs directory

```
deny from all
```



