<?php

# Requires these 3 variables in .htenv.php
# $slack_webhook_url = // web url from Slack website
# $oauth_token= // oAuth token from slack website
# $icon_url= // local icon
$GLOBALS['DEBUG'] = 0;

require_once("class_App.php");
require_once("class_Bot.php");
require_once("createNewBot.php");
require_once("SlackIO.php");

$app = new App($_POST || $args);
$bot = createNewBot($app);
$botResponseText = $bot->ask($app->text);
$bot->printInfo();
sendMessage($app, $botResponseText);
