<?php

# Get a thread of replies with the parent at the top and the children next in the array
# If a ts points to a child item in a thread then we only get back one message, but if 
# pull the parent then we get it all.

## local in memory cache
$GBL_SLACK_CONVERSATIONS = [];

## TODO handle getting full conversation here for a single thread, should mostly be short < 200 messages anyway

function getConversationRepliesFromSlack($channel, $ts, $latest = null) {
    global $GBL_SLACK_CONVERSATIONS;
    savelog("Retrieving conversation from slack (channel=$channel, timestamp=$ts)");

    $key = $channel."-".$ts;
    if ( isset($GBL_SLACK_CONVERSATIONS[$key]) ) {
        savelog("Using cached version for key $key");
        return $GBL_SLACK_CONVERSATIONS[$key];
    }
    savelog("Cache miss, sending request to slack");

    $url="https://slack.com/api/conversations.replies";
    $method="POST";
    $contentType="application/x-www-form-urlencoded";    

    $data = array(
        "channel" => $channel,
        "ts" => $ts,
        "limit" => 5,
        "inclusive" => true
    );

    if ( isset($latest) ) {
        $data['latest'] = $latest;
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
    savelog($result);
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