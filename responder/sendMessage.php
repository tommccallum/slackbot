<?php

function sendMessage($msg)
{
    print("Sending message:\n");
    var_dump($msg);

    $url="https://slack.com/api/chat.postMessage";
    $method="POST";
    $contentType="application/json";

    $json = json_encode($msg);
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
    curl_close($slack_call);
    print("Server response:\n");
    var_dump($result);
    if (substr($result, 0, 2) == "no") {
        return (false);
    }
    return (true);
}
