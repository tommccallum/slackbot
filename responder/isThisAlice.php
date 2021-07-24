<?php

require_once __DIR__ . '/../vendor/autoload.php';

function isThisAlice($userId) {    
    $collection = (new MongoDB\Client)->slackbot->users;
    $result = $collection->findOne(["id" => $userId]);
    if ( isset($result) ) {
        if ( $result['is_bot'] ) {
            if ( $result['profile']['first_name'] === "Alice" ) {
                return true;
            }
        }
    }
    return false;
}

// null or the user profile
function whoami($userId) {    
    if ( is_array($userId) ) {
        $result = [];
        $collection = (new MongoDB\Client)->slackbot->users;
        foreach($userId as $u) {
            $result[] = $collection->findOne(["id" => $u]);
        }
        return $result;
    }
    $collection = (new MongoDB\Client)->slackbot->users;
    $result = $collection->findOne(["id" => $userId]);
    return $result;
}