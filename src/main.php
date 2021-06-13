<?php

# Requires these 3 variables in .htenv.php
# $slack_webhook_url = // web url from Slack website
# $oauth_token= // oAuth token from slack website
# $icon_url= // local icon
$GLOBALS['DEBUG'] = 1;

if ( file_exists(".htenv.php") ) {
    require_once(".htenv.php");
} else if ( file_exists("../.htenv.php") ) {
    require_once("../.htenv.php");
} else {
    print("Could not locate .htenv.php file with API information in.");
    exit(0);
}

require_once("class_App.php");
require_once("class_Bot.php");
require_once("createNewBot.php");
require_once("delta.php");
require_once("isLanguage.php");
require_once("readSimpsons.php");
require_once("SlackIO.php");
require_once("translateString.php");

$app = new App();
$bot = createNewBot($app);
$botResponseText = $bot->ask($app->text);
$bot->printInfo();
sendMessage($app, $botResponseText);
