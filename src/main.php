<?php

# Requires these 3 variables in .htenv.php
# $slack_webhook_url = // web url from Slack website
# $oauth_token= // oAuth token from slack website
# $icon_url= // local icon
$GLOBALS['DEBUG'] = 0;

require_once("autoload_environment.php");
require_once("logging.php");
require_once("class_App.php");
require_once("class_Bot.php");
require_once("createNewBot.php");
require_once("SlackIO.php");

autoload_environment();

savelog("Begin session");
try {
    $json = file_get_contents('php://input');
    savelog($json);
    if (isset($json)) {
        $args = json_decode($json);
    } else {
        if (count($_POST) > 0) {
            $args = $_POST;
        } else {
            $args = $argv;
        }
    }
    savelog($args);
    $app = new App($args);
    if ( isset($app->type) && $app->type == "url_verification" ) {
        savelog("Detected challenge");
        sendSlackChallengeResponse($app);
        savelog("End of session");
    } else {
        $bot = createNewBot($app);
        $botResponseText = $bot->ask($app->text);
        $bot->printInfo();
        sendMessage($app, $botResponseText);
        savelog("End of session");
    }
} catch ( Exception $ex ) {
    savelog($ex);
    savelog("End of session");
}
