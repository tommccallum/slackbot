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

# Set this to a number which you do not expect to ever reach. If it reached we die.
$MAX_LIMIT_ON_POSTS = 20;

$cwd = __DIR__;
$dataDir = $cwd . DIRECTORY_SEPARATOR . "posts";

print("Data Directory: ".$dataDir."\n");

#
# Attempt to load channels, users and research subjects first
#

$channels = (new MongoDB\Client)->slackbot->channels;
$n = $channels->count();
print("Found $n channels in database\n");
if ($n == 0) {
    die("[FATAL] No channels uploaded to database 'slackbot'.");
}
$users = (new MongoDB\Client)->slackbot->users;
$n = $users->count();
print("Found $n users in database\n");
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
    print("Processing ".$f."\n");
    $info = pathinfo($f);
    $ext = $info['extension'];
    if ($ext == "json") {
        $contents = file_get_contents($f);
        $jsonContents = json_decode($contents, true);
    } elseif ($ext == "csv") {
        $jsonContents = parseCsvIntoJson($f);
    } else {
        print("Ignoring $f\n");
    }
    
    foreach ($jsonContents as $item) {
        if ($item['time'] == "*") {
            $item['time'] = $timeFromNow;
        }
        if ($item['date'] == "*") {
            $item['date'] = $dateFromNow;
        }
        $requestedDateTime = $item['date'] . " " . $item['time'];
        print("[Date comparison] requested: $requestedDateTime actual: $oneMinuteFromNow\n");
        if ($requestedDateTime === $oneMinuteFromNow) {
            array_push($messagesToSend, $item);
        }
    }
}

# we also need to send dialogue initiating messages which are stored in "dialogues" directory
$dialogueManager = new DialogueCollection();
$dialogueManager->loadFromDirectory(__DIR__."/data/dialogues");
$dialogues = $dialogueManager->getMatchingDateTime($dateFromNow, $timeFromNow);
printf("Found %d dialogues that might be ready to send\n", count($dialogues));
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



printf("Found %d messages to send\n", count($messagesToSend));

$msgCount = count($messagesToSend);


#
# Send the messages after removing all tags
#
$msgSentCounter = 0;
$msgFailCounter = 0;
foreach ($messagesToSend as $message) {
    if ($message['channel_name'] === "*") {
        print("Posting to all members of users in research file.\n");

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
                print("User ".$user." not found in database.\n");
            }
        }
    } else {
        print("Looking up channel ".$message['channel_name']."\n");
        $document = $channels->findOne(['name' => $message['channel_name']]);

        if (isset($document)) {
            $channelId = $document['id'];
            print("Mapped ".$message['channel_name']." to ".$channelId."\n");
        } else {
            print("Failed to find channel_name ".$message['channel_name']."\n");
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

printf("Sent   Messages: %d\n", $msgSentCounter);
printf("Failed Messages: %d\n", $msgFailCounter);
