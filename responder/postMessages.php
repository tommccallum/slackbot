<?php

# Cronjob to post messages on day at particular time.
# Expected to be processed on a minute by minute schedule
require_once __DIR__ . '/../vendor/autoload.php';
require_once(__DIR__."/../.htenv.php");


# Set this to a number which you do not expect to ever reach. If it reached we die.
$MAX_LIMIT_ON_POSTS = 20;      


$cwd = __DIR__;
$dataDir = $cwd . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "posts";

print("Data Directory: ".$dataDir."\n");

function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

function parseCsvIntoJson($path) 
{
    $json = [];

    $csv = array_map('str_getcsv', file($path));
    array_walk($csv, function(&$a) use ($csv) {
      $a = array_combine($csv[0], $a);
    });
    array_shift($csv); # remove column header

    foreach( $csv as $row ) {
        $item = array(
            "date" => $row['Date'],
            "time" => $row['Time'],
            "channel_name" => $row['Channel'],
            "message" => $row['Message']
        );
        $json[count($json)] = $item;
    }
    return $json;
}

function replaceTags($str, $keyvalues) 
{
    if (isset($keyvalues['timestamp'])) {
        $str = preg_replace("/%dayofweek%/", date("l", $keyvalues['timestamp']), $str);
        $str = preg_replace("/%date%/", date("l jS F", $keyvalues['timestamp']), $str);
        $str = preg_replace("/%time%/", date("H:i", $keyvalues['timestamp']), $str);
    }
    if (isset($keyvalues['user'])) {
        $nameParts = explode(' ', $keyvalues['user']['real_name']);
        $str = preg_replace("/%name%/", $nameParts[0], $str);
        $str = preg_replace("/%firstname%/", $nameParts[0], $str);
        $str = preg_replace("/%surname%/", $nameParts[count($nameParts)-1], $str);
    }
    if (isset($keyvalues['me']) ) {
        // TODO replace %me.name% with name etc
        $hasMatches = preg_match_all("/%me\.(\w+)%/", $str, $matches);
        var_dump($matches);
        if ( $hasMatches ) {
            $full_text_that_matched_array = $matches[0];
            $text_that_matched_array = $matches[1];
            for($ii=0; $ii < count($full_text_that_matched_array); $ii++ ) {
                $replacement = $keyvalues['me']->get($text_that_matched_array);
                $str = preg_replace("/".$full_text_that_matched_array[$ii]."/", $replacement, $str);
            }
        }
    }
    return $str;
}

function sendMessage($msg) {
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
    if ( substr($result,0,2) == "no") {
        return ( false );
    }
    return ( true );
}

#
# Attempt to load channels, users and research subjects first
#

$channels = (new MongoDB\Client)->slackbot->channels;
$n = $channels->count();
print("Found $n channels in database\n");
if ( $n == 0 ) {
    die("[FATAL] No channels uploaded to database 'slackbot'.");
}
$users = (new MongoDB\Client)->slackbot->users;
$n = $users->count();
print("Found $n users in database\n");
if ( $n == 0 ) {
    die("[FATAL] No users uploaded to database 'slackbot'.");
}

$usersInResearchPath = $cwd."/../users_in_research.json";
if ( file_exists($usersInResearchPath) ) {
    $contents = file_get_contents($usersInResearchPath);
    $usersInResearchArray = json_decode($contents, true);
} else {
    die("[FATAL] You need to create 'users_in_research.json' file.");
}


#
# Get all files in the 'posts' directory
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

foreach( $result as $f ) {
    print("Processing ".$f."\n");
    $info = pathinfo($f);
    $ext = $info['extension'];
    if ( $ext == "json" ) {
        $contents = file_get_contents($f);
        $jsonContents = json_decode($contents, true);
    } else if ( $ext == "csv" ) {
        $jsonContents = parseCsvIntoJson($f);
    } else {
        print("Ignoring $f\n");
    }
    
    foreach( $jsonContents as $item ) {
        if ( $item['time'] == "*" ) {
            $item['time'] = $timeFromNow;
        }
        if ( $item['date'] == "*" ) {
            $item['date'] = $dateFromNow;
        }
        $requestedDateTime = $item['date'] . " " . $item['time'];
        print("[Date comparison] requested: $requestedDateTime actual: $oneMinuteFromNow\n");
        if ( $requestedDateTime === $oneMinuteFromNow ) {
            array_push($messagesToSend, $item);
        }
    }
}

printf("Found %d messages to send\n", count($messagesToSend));

$msgCount = count($messagesToSend);


#
# Send the messages after removing all tags
#
$msgSentCounter = 0;
$msgFailCounter = 0;
foreach( $messagesToSend as $message ) {
    if ( $message['channel_name'] === "*" ) {
        print("Posting to all members of users in research file.\n");

        foreach( $usersInResearchArray as $user ) {
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
                if ( $ok ) {
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

        if ( isset($document) ) {
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
        if ( $ok ) {
            $msgSentCounter++;
        } else {
            $msgFailCounter++;
        }
    }

    if ( $msgSentCounter + $msgFailCounter > $MAX_LIMIT_ON_POSTS ) {
        die("Something has gone wrong, we should not have more than at most 20 messages to send per session.\n");
    }
}

printf("Sent   Messages: %d\n", $msgSentCounter);
printf("Failed Messages: %d\n", $msgFailCounter);


