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
$collection = (new MongoDB\Client(null, [], options]))->slackbot->events;


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

        if ($bot->shouldBotReplyToEvent() === false) {
            savelog("Ignoring event as not one which Alice will reply to.");

            # update current message as replied to
            $updatedResult = $collection->updateOne([ "_id" => $event['_id']], ['$set' => [
                    'slackbot.replied_to' => true,
                    'slackbot.action' => "ignored, failed 'shouldAliceReplyToEvent' check"
                    ]]);

            if ($updatedResult->getMatchedCount() == 1 && $updatedResult->getModifiedCount() == 1) {
                # success
                savelog("Successfully saved update to event");
            } else {
                savelog("An error occurred updating message after response was sent. (_id: ".$msg['_id'].").");
            }
            continue;
        }

        savelog("Alice should reply to this message");
        loadDialogue($bot);
        loadIntents($bot);
        $botResponseText = $bot->handle();
        if (isset($botResponseText)) {
            if (!is_array($botResponseText)) {
                $botReponseText = [ $botResponseText ];
            }
            foreach ($botResponseText as $text) {
                savelog($text);
                $bot->printInfo();
                sendMessage($app, $text);
            }
            # Update current message as replied to
            # TODO Do we want to also mark any messages in the chain BEFORE this one as having been replied to?
            #       We could restrict ourselves to replying to only those with a @Alice in but that seems undesirable.
            $updatedResult = $collection->updateOne([ "_id" => $event['_id']], ['$set' => [
            'slackbot.replied_to' => true,
            'slackbot.action' => 'reply',
            'slackbot.responseText' => $botResponseText
            ]]);

            if ($updatedResult->getMatchedCount() == 1 && $updatedResult->getModifiedCount() == 1) {
                # success
                savelog("Successfully saved update to event");
            } else {
                savelog("An error occurred updating message after response was sent. (_id: ".$msg['_id'].").");
            }
        } else { // else the user is not expecting a response to this event
            savelog("No response sent in response to this event.");
        }
    }

    savelog("End of replyToMessages session");

    if ($watchLoop) {
        sleep($secondsBetweenSessions);
    }
}
