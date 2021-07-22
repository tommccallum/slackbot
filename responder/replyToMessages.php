<?php

#
# Replies to messages in the database
# This is NOT the same as postMessages which posts scheduled messages. 
#

require_once __DIR__ . '/../vendor/autoload.php';
require_once "autoload.php";

## this line MUST be after the autoload.php
$LOG_PATH = __DIR__ . "/../logs/replyToMessages.log";

$collection = (new MongoDB\Client)->slackbot->events;

# sort by latest first so that we are always replying to the latest message in a thread.
savelog("Starting replyToMessages session");

# TODO avoid replying to an updated message if we pick an old one

while ( true ) {
    # get next most recent record
    $event = $collection->findOne(['slackbot.replied_to' => false, 'event.type' => "message"], ['sort' => ['event.ts' => 1 ]]);
    if ( !isset($event) ) {
        break;
    }

    savelog("Handling queued message");
    savelog(json_encode($event));

    if ( shouldAliceReplyToEvent($event) === false ) {
        savelog("Ignoring event as not one which Alice will reply to.");
        continue;
    }
    savelog("Alice should reply to this message");
    $app = new App($event);

    getConversation($app);

    $bot = createNewBot($app);
    $botResponseText = $bot->handle($app);
    if (isset($botResponseText)) {
        savelog($botResponseText);
        $bot->printInfo();
        sendMessage($app, $botResponseText);
        
        # update current message as replied to
        $updatedResult = $collection->updateOne([ "_id" => $msg['_id'], ['$set' => [
            'slackbot.replied_to' => true, 
            'slackbot.responseText' => $botResponseText 
            ]]] );

        if ( $updatedResult->getMatchedCount() == 1 && $$updatedResult->getModifiedCount() == 1 ) {
            # success
            savelog("Successfully saved update to event");
        } else {
            savelog("An error occurred updating message after response was sent. (_id: ".$msg['_id'].").");
        }
    }  else { // else the user is not expecting a response to this event
        savelog("No response sent in response to this event.");
    }
}

savelog("End of replyToMessages session");