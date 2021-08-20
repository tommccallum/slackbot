<?php

function getBotInfo($botId)
{
    $botInfo = getBotInfoFromMongo($botId);
    if (!isset($botInfo) || $botInfo === false) {
        $botInfo = getBotInfoFromSlack($botId);
        saveBotInfo($botId, $botInfo);
    }
    return $botInfo;
}

function saveBotInfo($botId, $botInfo)
{
    global $mongodb;
    $collection = $mongodb->slackbot->bots;
    $collection->replaceOne(["id" => $botId], $botInfo, [ "upsert" => true]);
}

function getBotInfoFromMongo($botId)
{
    global $mongodb;
    $collection = $mongodb->slackbot->bots;
    $result = $collection->findOne(["id" => $botId]);
    if (isset($result)) {
        return $result;
    }
    return false;
}

function getBotInfoFromSlack($botId)
{
    savelog("Retrieving bot info from slack (id=$botId)");
    $url="https://slack.com/api/bots.info";
    $method="POST";
    $contentType="application/x-www-form-urlencoded";

    $data = array(
        "bot" => $botId
        // team_id not required currently
    );
    
    $postfields_data = "";
    foreach ($data as $key => $value) {
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
    if (substr($result, 0, 2) == "no") {
        savelog("[ERROR] bots.info failed with error: ".$result);
        return (false);
    }
    $json = json_decode($result, true);
    if ($json['ok'] === false) {
        savelog("[API ERROR] bots.info failed with error: ".$json['error']);
        return (false);
    }
    return ($json["bot"]);
}
