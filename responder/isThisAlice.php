<?php

require_once __DIR__ . '/../vendor/autoload.php';

function getAliceId()
{
    global $mongodb;
    $collection = $mongodb->slackbot->users;
    $result = $collection->findOne(["is_bot" => true, "profile.first_name" => "Alice"]);
    return $result['id'];
}


function isThisAlice($userId)
{
    global $mongodb;
    $collection = $mongodb->slackbot->users;
    $result = $collection->findOne(["id" => $userId]);
    if (isset($result)) {
        if ($result['is_bot']) {
            if ($result['profile']['first_name'] === "Alice") {
                return true;
            }
        }
    }
    return false;
}

// null or the user profile
function whoami($userId)
{
    global $mongodb;
    if (is_array($userId)) {
        $result = [];
        $collection = $mongodb->slackbot->users;
        foreach ($userId as $u) {
            $result[] = $collection->findOne(["id" => $u]);
        }
        return $result;
    }
    $collection = $mongodb->slackbot->users;
    $result = $collection->findOne(["id" => $userId]);
    return $result;
}
