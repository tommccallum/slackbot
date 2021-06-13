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
    $requestMethod = null;
    if ( isset($_SERVER['REQUEST_METHOD']) ) {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
    }
    savelog($requestMethod);


    $contentType = null;
    if ( isset($_SERVER['CONTENT_TYPE']) ) {
        $contentType = $_SERVER['CONTENT_TYPE'];
    }
    if (!isset($contentType) || strlen($contentType) == 0) {
        if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
            $contentType = $_SERVER['HTTP_CONTENT_TYPE'];
        }
    }
    savelog($contentType);
    
    $requestBody = file_get_contents('php://input');
    savelog($requestBody);

    if ( $requestMethod == "GET" ) {
        // get request?
        $args = $_GET;
    } else if ( $requestMethod == "POST" ) {
        if ( $contentType == "application/json" ) {
            $args = json_decode($requestBody, true);
        } else {
            $args = $_POST;
        }
    } else {
        $args = $argv;
    }
    savelog($args);


    $app = new App($args);
    if ( isset($app->type) && $app->type == "url_verification" ) {
        savelog("Detected challenge");
        sendSlackChallengeResponse($app);
        savelog("End of session");
    } else {
        // implement the slack security procedure
        if ( !isset($_SERVER['X-Slack-Request-Timestamp']) ) {
            if (!isset($_SERVER['HTTP_X_Slack_Request_Timestamp'])) {
                throw new Exception("Slack request timestamp not found, discarding request");
            } else {
                $slackRequestTimestamp = $_SERVER['HTTP_X_Slack_Request_Timestamp'];
            }
        } else {
            $slackRequestTimestamp = $_SERVER['X-Slack-Request-Timestamp'];
        }
        savelog("Slack Request Timestamp: " . $slackRequestTimestamp);

        if ( !isset($_SERVER['X-Slack-Signature']) ) {
            if ( !isset($_SERVER['HTTP_X_SLACK_SIGNATURE']) ) {
                throw new Exception("Slack signature not found, discarding request");
            } else {
                $slackSignature = $_SERVER['HTTP_X_SLACK_SIGNATURE'];    
            }
        } else {
            $slackSignature = $_SERVER['X-Slack-Signature'];
        }

        
        if ( abs(time() - $slackRequestTimestamp ) > 60 * 5 ) {
            throw new Exception("slack request timestamp over 5 minutes old, discarding request");
        }
        $slackSigningSecret = SLACK_SIGNING_SECRET;
        $sig_basestring = 'v0:' . $slackRequestTimestamp . ':' . $requestBody;
        $signature = 'v0=' . hash('sha256', $slackSigningSecret,$sig_basestring);
        
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
