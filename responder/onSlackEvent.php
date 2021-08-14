<?php

function onSlackEvent($app, $bot, $event, $collection = null)
{
    if ($bot->shouldBotReplyToEvent() === false) {
        savelog("Ignoring event as not one which Alice will reply to.");

        # update current message as replied to
        if (isset($collection)) {
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
        }
        return null;
    }

    savelog("Alice should reply to this message");
    loadDialogue($bot);
    loadIntents($bot);
    $botResponseText = $bot->handle();
    if (isset($botResponseText)) {
        if (!is_array($botResponseText)) {
            $botResponseText = [ $botResponseText ];
        }
        foreach ($botResponseText as $text) {
            savelog($text);
            $bot->printInfo();
            sendMessage($app, $text);
        }
        # Update current message as replied to
        # TODO Do we want to also mark any messages in the chain BEFORE this one as having been replied to?
        #       We could restrict ourselves to replying to only those with a @Alice in but that seems undesirable.
        if (isset($collection)) {
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
        }
        return true;
    } else { // else the user is not expecting a response to this event
        savelog("No response sent in response to this event.");
    }
    return null;
}
