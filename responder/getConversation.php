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
function shouldAliceReplyToEvent($event) {
    if ( !isset($event['event']) ) {
        return false;
    }
    if ( $event['event']['type'] != "message" ) {
        return false;
    }
    $message = $event['event'];
    if ( isset($message['thread_ts'])
        && $message['ts'] != $message['thread_ts'] ) {
        # this event is a child event
        # we need to get the parent 
        # we should get the parent from our database if we can but for now we get directly from slack.
        $conversation = getConversationRepliesFromSlack($message['channel'], $message['thread_ts']);
        if ( didAliceGetMentionedInThisThreadAnywhere($conversation) ) {
            return true;
        }
        if ( didAliceStartThisConversation($conversation) ) {
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
function getConversation(&$app) {
    # We know that we will either be passed the top message of a new thread in which case
    # conversation.replies is the call we want.  Or if they are having a conversation without
    # using threads, then its conversation.history which we want.

    $conversation = getConversationRepliesFromSlack($app->channelId, $app->getParentThread());
    $app->setConversation($conversation);
}


/**
 * Look for any "mentions" of Alice in the entire thread
 *
 * @param [type] $conversation
 * @return void
 */
function didAliceGetMentionedInThisThreadAnywhere($conversation) 
{
    if ( isset($conversation['messages']) ) {
        foreach( $conversation['messages'] as $message ) {
            if ( isset($message['blocks']) ) {
                $blocks = $message['blocks'];
                foreach($blocks as $block) {
                    if ( isset($block['elements']) ) {
                        $elements = $block['elements'];
                        foreach( $elements as $element ) {
                            if ( $element['type'] == "user" ) {
                                if (isset($element['user_id'])) {
                                    $userId = $element['user_id'];
                                    if ( isThisAlice($userId) ) {
                                        return true;
                                    }
                                }
                            }
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
    $initiatingUser = $conversation['messages'][0]['user'];
    if ( isThisAlice($initiatingUser) ) {
        return true;
    } 
    return false;
}