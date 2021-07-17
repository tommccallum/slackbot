# Slackbot

Slackbot is a Virtual Assistant that has been designed to deliver a Digital Narrative to one or more registered members.

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



