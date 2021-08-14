<?php

#
# Replies to messages in the database
# This is NOT the same as postMessages which posts scheduled messages.
#

$watchLoop = false;
$outputLogToScreen = false;
$secondsBetweenSessions = 20;
$ii=1;
$argc = count($argv);
while ($ii < $argc) {
    if ($argv[$ii] == "--loop") {
        $watchLoop = true;
        $outputLogToScreen = true;
    }
    $ii++;
}


require_once __DIR__ . '/../vendor/autoload.php';
require_once "autoload.php";

$GLOBALS['DEBUG'] = 0;

## this line MUST be after the autoload.php
$LOG_PATH = __DIR__ . "/../logs/replyToMessages.log";

$options = ["typeMap" => ['root' => 'array', 'document' => 'array']];
$collection = (new MongoDB\Client(null, [], $options))->slackbot->events;


while ($watchLoop) {
    # sort by latest first so that we are always replying to the latest message in a thread.
    savelog("Starting replyToMessages session");

    # TODO avoid replying to an updated message if we pick an old one

    while (true) {
        # get least recent event that has not been responded to, sorts the events in ascending time order
        $event = $collection->findOne(['slackbot.replied_to' => false, 'event.type' => "message"], ['sort' => ['event.ts' => 1 ]]);
        if (!isset($event)) {
            break;
        }

        savelog("Handling queued message");
        savelog(json_encode($event));

        $app = new App($event);
        $app->botSelectionName = "Alice";
        $bot = createNewBot($app);

        onSlackEvent($app, $bot, $event, $collection);
    }

    savelog("End of replyToMessages session");

    if ($watchLoop) {
        sleep($secondsBetweenSessions);
    }
}
