<?php

/**
 * Post a message directly to a user
 * @see https://api.slack.com/methods/chat.postMessage
 * @see https://api.slack.com/docs/messages/builder
 */

require_once(".htenv.php");

$url="https://slack.com/api/chat.postMessage";
$method="POST";
$contentType="application/json";

$channelOrUser = "UUNQNAB24"; // this must be the member id not the user's name
$message = "Testing direct message from Alice";

$data = array(
    "channel" => $channelOrUser,
    "text" => $message,
    "mrkdwn" => true
);

$json = json_encode($data);
$slack_call = curl_init($url);
curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($slack_call, CURLOPT_POSTFIELDS, $json);
curl_setopt($slack_call, CURLOPT_CRLF, true);
curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
    $slack_call,
    CURLOPT_HTTPHEADER,
    array(
        "Content-Type: " . $contentType . "; charset=utf-8",
        "Content-Length: " . strlen($json),
        "Authorization: Bearer " . SLACK_OAUTH_TOKEN
    )
);
$result = curl_exec($slack_call);
// $info = curl_getinfo($slack_call);
// print_r($info);
curl_close($slack_call);
print("Callback result:\n");
var_dump($result);