<?php

# Cronjob to post messages on day at particular time.
# Expected to be processed on a minute by minute schedule
# This is NOT the same as replyToMessages that replies to a users input

require_once __DIR__ . '/../vendor/autoload.php';
require_once(__DIR__."/include_source.php");
spl_autoload_register(function ($className) {
    $locations = [
        __DIR__,
        __DIR__."/bots",
        __DIR__."/../common",
        __DIR__."/../common/bots",
    ];

    foreach ($locations as $location) {
        $phpFilePath = $location . "/class_" . $className . '.php';
        if (!class_exists($className) && file_exists($phpFilePath)) {
            require($phpFilePath);
            return;
        }
    }
});

include_source("autoload_environment.php");
include_source("getDirContents.php");
include_source("sendMessage.php");
include_source("replaceTags.php");
include_source("parseCsvIntoJson.php");

autoload_environment();

$isTestModeActive = false;
$debugMode = false;
foreach ($argv as $a) {
    if ($a == "--test") {
        $isTestModeActive = true;
        print("[WARNING] Test mode is activated, allowing messages with * as time to be sent.\n");
    } elseif ($a == "--debug") {
        $debugMode = true;
    }
}

function debugPostMsg($msg)
{
    global $debugMode;
    if ($debugMode) {
        print($msg);
    }
}

# Set this to a number which you do not expect to ever reach. If it reached we die.
$MAX_LIMIT_ON_POSTS = 20;

$cwd = __DIR__;
$dataDir = $cwd . DIRECTORY_SEPARATOR . "posts";

debugPostMsg("Data Directory: ".$dataDir."\n");


#
# Attempt to load channels, users and research subjects first
#

$channels = (new MongoDB\Client)->slackbot->channels;
$n = $channels->count();
debugPostMsg("Found $n channels in database\n");
if ($n == 0) {
    die("[FATAL] No channels uploaded to database 'slackbot'.");
}
$users = (new MongoDB\Client)->slackbot->users;
$n = $users->count();
debugPostMsg("Found $n users in database\n");
if ($n == 0) {
    die("[FATAL] No users uploaded to database 'slackbot'.");
}

$usersInResearchPath = $cwd."/../users_in_research.json";
if (file_exists($usersInResearchPath)) {
    $contents = file_get_contents($usersInResearchPath);
    $usersInResearchArray = json_decode($contents, true);
} else {
    die("[FATAL] You need to create 'users_in_research.json' file.");
}


#
# Get all files in the 'posts' directory, these are just flat messages that have no further structure
#
$result = getDirContents($dataDir);

#
# Sort through and look for ones that we want to send this session.
#
$messagesToSend = array();
$datetime = strtotime("+1 minute");
$oneMinuteFromNow = date("d/m/y H:i:00", $datetime);
$bits = explode(' ', $oneMinuteFromNow);
$dateFromNow = $bits[0];
$timeFromNow = $bits[1];

foreach ($result as $f) {
    debugPostMsg("Processing ".$f."\n");
    $info = pathinfo($f);
    $ext = $info['extension'];
    if ($ext == "json") {
        $contents = file_get_contents($f);
        $jsonContents = json_decode($contents, true);
    } elseif ($ext == "csv") {
        $jsonContents = parseCsvIntoJson($f);
    } else {
        debugPostMsg("Ignoring $f\n");
    }
    
    foreach ($jsonContents as $item) {
        if ($isTestModeActive && $item['time'] == "*") {
            $item['time'] = $timeFromNow;
        }
        if ($item['date'] == "*") {
            $item['date'] = $dateFromNow;
        }
        $requestedDateTime = $item['date'] . " " . $item['time'];
        debugPostMsg("[Date comparison] requested: $requestedDateTime actual: $oneMinuteFromNow\n");
        if ($requestedDateTime === $oneMinuteFromNow) {
            array_push($messagesToSend, $item);
        }
    }
}

# we also need to send dialogue initiating messages which are stored in "dialogues" directory
$dialogueManager = new DialogueCollection();
$dialogueManager->loadFromDirectory(__DIR__."/data/dialogues");
$dialogues = $dialogueManager->getMatchingDateTime($dateFromNow, $timeFromNow, $isTestModeActive);
debugPostMsg("Found ".count($dialogues)." dialogues that might be ready to send\n");
foreach ($dialogues as $dialogue) {
    $text = $dialogue->getInitialText();
    if (isset($text)) {
        $msg = [
            "channel_name" => "*",
            "message" => $text
        ];
        array_push($messagesToSend, $msg);
    }
}


debugPostMsg("Found ".count($messagesToSend)." messages to send\n");
$msgCount = count($messagesToSend);


#
# Send the messages after removing all tags
#
$msgSentCounter = 0;
$msgFailCounter = 0;
foreach ($messagesToSend as $message) {
    if ($message['channel_name'] === "*") {
        debugPostMsg("Posting to all members of users in research file.\n");

        foreach ($usersInResearchArray as $user) {
            $document = $users->findOne(['name' => $user]);
            if (isset($document)) {
                $args = [
                    "timestamp" => $datetime,
                    "user" => $document
                ];
                $msgToSend = [];
                $msgToSend['channel'] = $document['id'];
                $msgToSend['text'] = replaceTags($message['message'], $args);
                $msgToSend['mrkdwn'] = true;
                $ok = sendMessage($msgToSend);
                if ($ok) {
                    $msgSentCounter++;
                } else {
                    $msgFailCounter++;
                }
            } else {
                print("[ERROR] User ".$user." not found in database.\n");
            }
        }
    } else {
        debugPostMsg("Looking up channel ".$message['channel_name']."\n");
        $document = $channels->findOne(['name' => $message['channel_name']]);

        if (isset($document)) {
            $channelId = $document['id'];
            debugPostMsg("Mapped ".$message['channel_name']." to ".$channelId."\n");
        } else {
            print("[ERROR] Failed to find channel_name ".$message['channel_name']."\n");
            continue;
        }
        $args = [
            "timestamp" => $datetime
        ];
        
        $msgToSend = [];
        $msgToSend['channel'] = $channelId;
        $msgToSend['text'] = replaceTags($message['message'], $args);
        $msgToSend['mrkdwn'] = true;
        $ok = sendMessage($msgToSend);
        if ($ok) {
            $msgSentCounter++;
        } else {
            $msgFailCounter++;
        }
    }

    if ($msgSentCounter + $msgFailCounter > $MAX_LIMIT_ON_POSTS) {
        die("Something has gone wrong, we should not have more than at most 20 messages to send per session.\n");
    }
}

if ($msgSentCounter > 0 || $debugMode) {
    printf("Sent   Messages: %d\n", $msgSentCounter);
}
if ($msgFailCounter > 0 || $debugMode) {
    printf("Failed Messages: %d\n", $msgFailCounter);
}
