<?php

function sendMessage($app, $message) {
    if ( $app->sendToSlack ) {
        sendSlackMessage($app, $message);
    } else {
        if (isset($response)) {
            var_dump($response);
        }
        var_dump($message);
    }
}

function sendSlackChallengeResponse($app) {
    if ( !isset($app->challenge) ) {
        throw new Exception("challenge code not found");
    }
    print($app->challenge);
}

function sendSlackReaction($app, $emoji)
{
    if ( !defined("SLACK_WEBHOOK_URL") ) {
        autoload_environment();
    }
    savelog("Sending reaction to Slack (".$app->channelId.")");

    $data = array(
        "channel" => $app->channelId,
	    "name" => $emoji,
        "timestamp" => $app->getThreadId()
	);

    savelog($data);

    $json = json_encode($data);
    $slack_call = null;
    if ( isset($app->event['channel_type']) && $app->event['channel_type'] == "im" ) {
        savelog("Sending message to im (".$app->channelId.")");
        $slack_call = curl_init(SLACK_DM_URL);
    } else if ( isset($app->event['channel_type']) && $app->event['channel_type'] == "channel" ) {
        savelog("Sending message to channel (".$app->channelId.")");
        $slack_call = curl_init(SLACK_WEBHOOK_URL);
    } else {
        savelog("Unrecognised message, not sure where to respond to. (channel:".$app->channelId.", type:".( isset($app->event) ? $app->event['channel_type'] : null).")");
        return (null);
    }
    curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($slack_call, CURLOPT_POSTFIELDS, $json);
    curl_setopt($slack_call, CURLOPT_CRLF, true);
    curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $slack_call,
        CURLOPT_HTTPHEADER,
        array(
            "Content-Type: application/json; charset=utf-8",
            "Content-Length: " . strlen($json),
            "Authorization: Bearer " . SLACK_OAUTH_TOKEN
        )
    );
    $result = curl_exec($slack_call);
    curl_close($slack_call);

    savelog($result);
    if ( $result == "no_active_hooks" ) {
        throw new Exception("Slack reported 'No active hooks'");
    }
    return ($result);
}

function sendSlackMessage($app, $message) {
    if ( !defined("SLACK_WEBHOOK_URL") ) {
        autoload_environment();
    }
    savelog("Sending response back to Slack (".$app->channelId.")");

    $data = array(
        "username" => "alice",
		"channel" => $app->channelId,
		"text" => $message,
		"mrkdwn" => true,
		"icon_url" => SLACK_ICON_URL,
		"attachments" => null
	);

    if ( $app->isReplyToThread() ) {
        $data['thread_ts'] = $app->getThreadId();
    } else if ( $app->isEvent() ) {
        $data['thread_ts'] = $app->getThreadId();
    }

    savelog($data);

    $json = json_encode($data);
    $slack_call = null;
    if (isset($app->responseUrl)) {
        savelog("Sending message to reponseUrl (".$app->responseUrl.")");
        $slack_call = curl_init($app->responseUrl);
    } else if ( isset($app->event['channel_type']) && $app->event['channel_type'] == "im" ) {
        savelog("Sending message to im (".$app->channelId.")");
        $slack_call = curl_init(SLACK_DM_URL);
    } else if ( isset($app->event['channel_type']) && $app->event['channel_type'] == "channel" ) {
        savelog("Sending message to channel (".$app->channelId.")");
        $slack_call = curl_init(SLACK_WEBHOOK_URL);
    } else {
        savelog("Unrecognised message, not sure where to respond to. (channel:".$app->channelId.", type:".( isset($app->event) ? $app->event['channel_type'] : null).")");
        return (null);
    }
    curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($slack_call, CURLOPT_POSTFIELDS, $json);
    curl_setopt($slack_call, CURLOPT_CRLF, true);
    curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $slack_call,
        CURLOPT_HTTPHEADER,
        array(
            "Content-Type: application/json; charset=utf-8",
            "Content-Length: " . strlen($json),
            "Authorization: Bearer " . SLACK_OAUTH_TOKEN
        )
    );
    $result = curl_exec($slack_call);
    curl_close($slack_call);

    savelog($result);
    if ( $result == "no_active_hooks" ) {
        throw new Exception("Slack reported 'No active hooks'");
    }
    return ($result);
}