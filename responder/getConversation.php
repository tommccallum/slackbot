<?php

# We may want to use some kind of cache, but lets try without to begin with and
# see how we go as using the fresh stuff is always better.

// function saveConversation($channel,$ts, $messages)
// {
//     $d = __DIR__."/cache/$channel";
//     if ( !file_exists($d) || !is_dir($d) ) {
//         mkdir($d, 0777, true);
//     }
//     $path = $d.DIRECTORY_SEPARATOR.$ts;
//     file_put_contents($path, json_encode($messages));
// }

// function getConversationFromCache($channel,$ts) {
//     $d = __DIR__."/cache/$channel";
//     if ( !file_exists($d) || !is_dir($d) ) {
//         return null;
//     }
//     $path = $d . DIRECTORY_SEPARATOR . $ts;
//     if ( file_exists($path) ) {
//         $contents = file_get_contents($path);
//         $messages = json_decode($contents, true);
//         return $messages;
//     }
//     return null;
// }




#
# For simplicity we are going to do the following:
#   - only reply to threads where we have an app_mention
#   - always place our answers in a reply thread, not in the main conversation
# TODO extend this to be better at following the conversation flow.
#

// Event as stored in MongoDB
function shouldAliceReplyToEvent($event)
{
    if (!isset($event['event'])) {
        return false;
    }
    if ($event['event']['type'] != "message") {
        return false;
    }
    $message = $event['event'];
    if (isset($message['thread_ts'])
        && $message['ts'] != $message['thread_ts']) {
        # this event is a child event
        # we need to get the parent
        # we should get the parent from our database if we can but for now we get directly from slack.
        $conversation = getConversationRepliesFromSlack($message['channel'], $message['thread_ts'], $message['ts']);
        if (didAliceGetMentionedInThisThreadAnywhere($conversation)) {
            return true;
        }
        if (didAliceStartThisConversation($conversation)) {
            return true;
        }
    } else {
        # IF this is a private IM conversation then we reply to everything as we are the only other
        #   user in the chat.
        if ($message['channel_type'] == "im") {
            return true;
        }
        if ($message['subtype'] == "channel_join") {
            return true;
        }
        if ($message['subtype'] == "message_changed") {
            return false;
        }

        # IF this is a channel conversation if its not an im then we don't normally reply to this unless
        #   they tag is (@Alice).
        $conversation = getConversationRepliesFromSlack($message['channel'], $message['ts']);
        if (didAliceGetMentionedInThisThreadAnywhere($conversation)) {
            return true;
        }
        if (didAliceStartThisConversation($conversation)) {
            return true;
        }
    }
    return false;
}

/**
 * Set the conversation context for the current message
 *
 * @param [type] $app
 * @return void
 */
function getConversation(&$app)
{
    # We know that we will either be passed the top message of a new thread in which case
    # conversation.replies is the call we want.  Or if they are having a conversation without
    # using threads, then its conversation.history which we want.

    $conversation = getConversationRepliesFromSlack($app->channelId, $app->getParentThread());
    $app->setConversation($conversation);
}

function checkElementsForUserID($parent)
{
    if (!isset($parent['elements'])) {
        return [];
    }
    $users = [];
    foreach ($parent['elements'] as $element) {
        $users = array_merge($users, checkElementsForUserID($element));
        if (isset($element['type'])) {
            if ($element['type'] == "user") {
                if (isset($element['user_id'])) {
                    array_push($users, $element['user_id']);
                }
            }
        }
    }
    return $users;
}


/**
 * Look for any "mentions" of Alice in the entire thread
 *
 * @param [type] $conversation
 * @return void
 */
function didAliceGetMentionedInThisThreadAnywhere($conversation)
{
    if (isset($conversation['messages'])) {
        foreach ($conversation['messages'] as $message) {
            if (isset($message['blocks'])) {
                $blocks = $message['blocks'];
                foreach ($blocks as $block) {
                    $usersArray = checkElementsForUserID($block);
                    $usersArray = array_unique($usersArray);
                    // TODO keeping in for now when we test @here and @everyone to see what happens
                    savelog("::didAliceGetMentionedInThisThreadAnywhere::");
                    savelog(json_encode($usersArray));
                    foreach ($usersArray as $userId) {
                        if (isThisAlice($userId)) {
                            return true;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function didAliceStartThisConversation($conversation)
{
    // TODO we could also use the parent_user_id field if it exists here.
    $initiatingUser = $conversation['messages'][0]['user'];
    if (isThisAlice($initiatingUser)) {
        return true;
    }
    return false;
}
