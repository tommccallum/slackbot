<?php

/**
 * Post a message directly to a user
 * @see https://api.slack.com/methods/chat.postMessage
 * @see https://api.slack.com/docs/messages/builder
 */

require_once("../.htenv.php");

$url="https://slack.com/api/conversations.list";
$method="POST";
$contentType="application/x-www-form-urlencoded";

$data = array(
    "exclude_archived" => "true",
    "limit" => 1000,
    "team_id" => "TUPQR1UBH",
    "types" => "public_channel, private_channel"
);

$postfields_data = "";
foreach( $data as $key => $value ) {
    if (strlen($postfields_data) > 0) {
        $postfields_data .= "&";
    }
    $postfields_data .= $key."=".urlencode($value);
}

#$json = json_encode($data);
$slack_call = curl_init($url);
curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($slack_call, CURLOPT_POSTFIELDS, $postfields_data);
curl_setopt($slack_call, CURLOPT_CRLF, true);
curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
    $slack_call,
    CURLOPT_HTTPHEADER,
    array(
        "Content-Type: " . $contentType . "; charset=utf-8",
        "Authorization: Bearer " . SLACK_OAUTH_TOKEN
    )
);
$result = curl_exec($slack_call);
// $info = curl_getinfo($slack_call);
// print_r($info);
$jsonOutput = json_encode($result);
file_put_contents("../channels_list.json", $jsonOutput);
// curl_close($slack_call);
// print("Callback result:\n");
// var_dump($result);