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

function sendSlackMessage($app, $message) {
    if ( !defined("SLACK_WEBHOOK_URL") ) {
        autoload_environment();
    }
    $data = array(
		"username" => "rainbow",
		"channel" => $app->channelId,
		"text" => $message,
		"mrkdwn" => true,
		"icon_url" => SLACK_ICON_URL,
		"attachments" => null
	);

    savelog($data);

    $json = json_encode($data);
    $slack_call = curl_init($app->responseUrl);
    curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($slack_call, CURLOPT_POSTFIELDS, $json);
    curl_setopt($slack_call, CURLOPT_CRLF, true);
    curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $slack_call,
        CURLOPT_HTTPHEADER,
        array(
            "Content-Type: application/json",
            "Content-Length: " . strlen($json)
        )
    );
    $result = curl_exec($slack_call);
    curl_close($slack_call);

    savelog($result);
    if ( $result == "no_active_hooks" ) {
        throw new Exception("Slack reported 'No active hooks'");
    }
}