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
    $requestBody = file_get_contents('php://input');
    savelog($requestBody);
    if (isset($requestBody)) {
        $args = json_decode($json, true);
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

        // implement the slack security procedure
        $slackRequestTimestamp = $_SERVER['X-Slack-Request-Timestamp'];
        savelog("Slack Request Timestamp: " . $slackRequestTimestamp);
        if ( abs(time() - $slackRequestTimestamp ) > 60 * 5 ) {
            throw new Exception("slack request timestamp over 5 minutes old, discarding request");
        }
        $slackSigningSecret = SLACK_SIGNING_SECRET;
        $sig_basestring = 'v0:' . $slackRequestTimestamp . ':' . $requestBody;
        $signature = 'v0=' . hash('sha256', $slackSigningSecret,$sig_basestring);
        $slackSignature = $_SERVER['X-Slack-Signature'];
        savelog("Computed hash: " . $signature);
        savelog("Received hash: " . $slackSignature);

        if ($signature === $slackSignature) {
            $bot = createNewBot($app);
            $botResponseText = $bot->ask($app->text);
            $bot->printInfo();
            sendMessage($app, $botResponseText);
            savelog("End of session");
        } else {
            throw new Exception("computed hash did not match the received hash");
        }
    }
} catch ( Exception $ex ) {
    savelog($ex);
    savelog("End of session");
}
