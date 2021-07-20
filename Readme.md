# Slackbot

Slackbot is a Virtual Assistant that has been designed to deliver a Digital Narrative to one or more registered members.

## Dependencies

Ensure you are using PHP 7.2 or above.

We are using mongodb database to store all the JSON information we use.  

### Install the mongodb driver

```
sudo apt install mongodb
sudo apt install php-pear
sudo apt install php-dev
sudo pecl install mongodb
```

Add this line to all php.ini files in /etc/php/*

```
extension=mongodb.so
```

### Use the mongodb php library

This will be installed by composer.   If you don't have this installed then do the following:

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/bin/composer
composer --self-update --2
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

## Install Monogdb on Fedora

Add the following to the /etc/yum.repos.d/mongodb.repo:

```
[Mongodb]
name=MongoDB Repository
baseurl=https://repo.mongodb.org/yum/redhat/8/mongodb-org/4.4/x86_64/
gpgcheck=1
enabled=1
gpgkey=https://www.mongodb.org/static/pgp/server-4.4.asc
```

Then on the command line:
```
sudo dnf -y install mongodb-org mongodb-org-server
sudo systemctl enable mongod.service 
sudo systemctl start mongod.service 
mongod --version
```

## Starting conversations

You can automate starting conversations by adding a scheduled post.  The postMessage.php backoffice script recognises JSON and CSV files with the following format:

```
"Date","Time","Channel","Message"
18/07/21,*,"virtual-assistant-dev","Hi everyone, its Alice here! Its %date% at %time% and I am working on something awesome!"
18/07/21,*,*,"Hi %name%, its Alice here! Its %date% at %time% and I am working on something awesome!"
18/07/21,10:00,*,"Hi %name%, its Alice here! Its %date% at %time% and I am working on something awesome!"
```

For json the keys are all in lowercase.

* If Date == * then it is sent everyday.
* Date must be in dd/mm/yy format.
* If Time == * then it is sent every time the script is run - only used really for debugging.
* Time must be in HH:MM format.  It expects Excel/OpenOffice to complete as HH:MM:00.
* If Channel == * then it sends it to all registered users for the research.

The messages can have the following tags:

* %date% gives the date as Friday 15th March
* %time% gives the time as 15:30
* %name% gives the first name of the real name.
* %firstname% gives the first name of the real name.
* %surname% gives the last name of the real name.

## Topic tracking

* If the message is in a thread then we assume its part of that topic area.
* If the previous message has a topic and the current message has no nouns then we assume its the same topic and continuing the thread.
* Otherwise we look for nouns in the message which will give us a clue as to what the topic is.  Learning outcome codes are treated as nouns.
* We look for a question word such as How, What, Why, When, Where and if not found is there a question mark.  We can also write a classifier to look for reverse parts of speech e.g. can I ...?
* We look for verbs (the intent of the sentence) so "Can you recommend a resource for learning HTML?"
* Given an intent we will then forward the request to be fulfilled.