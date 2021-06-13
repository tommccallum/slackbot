<?php

function sendMessage($app, $message) {
    if ( $app->isSlack ) {
        sendSlackMessage($app, $message);
    } else {
        if (isset($response)) {
            var_dump($response);
        }
        var_dump($message);
    }
}

function sendSlackMessage($app, $message) {
    $data = array(
		"username" => "rainbow",
		"channel" => $app->channelId,
		"text" => $message,
		"mrkdwn" => true,
		"icon_url" => $app->icon_url,
		"attachments" => null
	);

    $json = json_encode($data);
    $slack_call = curl_init($app->slack_webhook_url);
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

    // TODO check $result for success
    curl_close($slack_call);
}