<?php

/**
 * So just passing a channel id without a timestamp will get us the last N items.
 * We can pass the cursor back to it to get the next 5 if we require them.
 */

function getConversationHistoryFromSlack($channel, $ts = null, $cursor = null) {
    savelog("Retrieving conversation history from slack (channel=$channel, timestamp=$ts)");
    $url="https://slack.com/api/conversations.history";
    $method="POST";
    $contentType="application/x-www-form-urlencoded";    

    $data = array(
        "channel" => $channel,
        "limit" => 5,
        "inclusive" => true
    );
    if (isset($ts)) {
        $data['ts'] = $ts;
    }
    if (isset($cursor)) {
        $data['cursor'] = $ts;
    }
    
    $postfields_data = "";
    foreach( $data as $key => $value ) {
        if (strlen($postfields_data) > 0) {
            $postfields_data .= "&";
        }
        $postfields_data .= $key."=".urlencode($value);
    }

    $slack_call = curl_init($url);
    curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($slack_call, CURLOPT_POSTFIELDS, $postfields_data);
    curl_setopt($slack_call, CURLOPT_CRLF, true);
    curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $slack_call,
        CURLOPT_HTTPHEADER,
        array(
            "Content-Type: " . $contentType ,
            "Authorization: Bearer " . SLACK_OAUTH_TOKEN
        )
    );
    $result = curl_exec($slack_call);
    var_dump($result);
    if ( substr($result,0,2) == "no") {
        savelog("[ERROR] conversation.replies failed with error: ".$result);
        return ( false );
    }
    $json = json_decode($result, true);
    if ( $json['ok'] === false ) {
        savelog("[API ERROR] conversation.replies failed with error: ".$json['error']);
        return ( false );
    }
    savelog("Received ".count($json['messages'])." messages");
    return ( $json );
}