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