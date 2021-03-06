<?php

#
#  Listens for Slack events
#   replies to challenge requests
#   ignores messages that are sent by itself
#   stores all other messages in the events MongoDB queue
#

# Requires these 3 variables in .htenv.php
# $slack_webhook_url = // web url from Slack website
# $oauth_token= // oAuth token from slack website
# $icon_url= // local icon
$GLOBALS['DEBUG'] = 0;
$outputLogToScreen = false;

require_once("include_source.php");
include_source("autoload_environment.php");
include_source("logging.php");
include_source("class_App.php");
include_source("class_Bot.php");
include_source("createNewBot.php");
include_source("SlackIO.php");

autoload_environment();

savelog("Begin session");
savelog("Bot ID: ".BOT_ID);

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
    if ( $app->isSelf() ) {
        savelog("Own message received, nothing sent in response.");
        savelog("End of session");
    } else if ( $app->isChallenge() ) { # isset($app->type) && $app->type == "url_verification"
        savelog("Detected challenge");
        sendSlackChallengeResponse($app);
        savelog("End of session");
    } else {
        // implement the slack security procedure (https://api.slack.com/authentication/verifying-requests-from-slack)
        if ( !isset($_SERVER['X-Slack-Request-Timestamp']) ) {
            if (!isset($_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'])) {
                throw new Exception("Slack request timestamp not found, discarding request");
            } else {
                $slackRequestTimestamp = $_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'];
            }
        } else {
            $slackRequestTimestamp = $_SERVER['X-Slack-Request-Timestamp'];
        }
        if ( abs(time() - $slackRequestTimestamp ) > 60 * 5 ) {
            savelog("Slack Request Timestamp: " . $slackRequestTimestamp);
            throw new Exception("slack request timestamp over 5 minutes old, discarding request");
        }

        if ( !isset($_SERVER['X-Slack-Signature']) ) {
            if ( !isset($_SERVER['HTTP_X_SLACK_SIGNATURE']) ) {
                throw new Exception("Slack signature not found, discarding request");
            } else {
                $slackSignature = $_SERVER['HTTP_X_SLACK_SIGNATURE'];    
            }
        } else {
            $slackSignature = $_SERVER['X-Slack-Signature'];
        }

        $slackSigningSecret = SLACK_SIGNING_SECRET;
        $sig_basestring = 'v0:' . $slackRequestTimestamp . ':' . $requestBody;
        $signature = 'v0=' . hash_hmac('sha256', $sig_basestring, $slackSigningSecret);
        
        if ($signature === $slackSignature) {
            savelog("Received message");
            savelog($args);

            # save event in database to be replied to
            if ( file_exists(__DIR__.DIRECTORY_SEPARATOR."vendor") ) {
                require_once __DIR__ . '/vendor/autoload.php';
            } else {
                require_once __DIR__ . '/../vendor/autoload.php';
            }
            $collection = (new MongoDB\Client)->slackbot->events;
            $args['slackbot'] = [
                "incoming" => true,
                "replied_to" => false,
                "timestamp" => time()
            ];
            $insertOneResult = $collection->insertOne($args);
            savelog("Inserted ".$insertOneResult->getInsertedCount()." document(s)");

        } else {
            savelog("Computed hash: " . $signature);
            savelog("Received hash: " . $slackSignature);
            throw new Exception("computed hash did not match the received hash");
        }
        savelog("End of session");
    }
} catch ( Exception $ex ) {
    savelog($ex);
    savelog("End of session");
}
