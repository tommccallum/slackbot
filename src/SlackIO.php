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

function autoload_secrets() {
    if ( file_exists(".htenv.php") ) {
        require_once(".htenv.php");
    } else if ( file_exists("../.htenv.php") ) {
        require_once("../.htenv.php");
    } else {
        print("Could not locate .htenv.php file with API information in.");
        exit(0);
    }
    
}

function sendSlackMessage($app, $message) {
    autoload_secrets();
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
    $slack_call = curl_init(SLACK_WEBHOOK_URL);
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
    savelog($result);
    
    curl_close($slack_call);

    if ( $result == "no_active_hooks" ) {
        throw new Exception("Slack reported 'No active hooks'");
    }
    var_dump($result);
    // TODO check $result for success
    
}