<?php

// Stores the conversation state
// incoming message -> outgoing state data
// saves to mongo db

// This is not the same as the events collection which is
// the incoming pipe.  This is like a session state.

class ConversationState
{
    protected $data = [];
    protected $botId = null;

    /**
     * Undocumented function
     *
     * @param [type] $threadOrMessage
     * @param [type] $botId                 the slack user id of the bot, this is so we can filter for the messages we send as we are not interested in those.
     */
    public function __construct($threadOrEvent = null, $botId = null, $channel="test")
    {
        $this->botId = $botId;
        if (isset($threadOrEvent)) {
            if (is_array($threadOrEvent)) {
                // we want the PARENT thread id if it exists, if we just have a ts then it is already a parent thread
                $threadId = $threadOrEvent['ts'];
                $isChildMessage = false;
                if (isset($threadOrEvent['thread_ts'])) {
                    $threadId = $threadOrEvent['thread_ts'];
                    $isChildMessage = true;
                }
                $this->data['thread_id'] = $threadId;
                if (!isset($threadOrEvent['channel'])) {
                    throw new \Exception("no channel found in message");
                }
                $this->data['channel'] = $threadOrEvent['channel'];

                // we load the conversation from the database if it exists
                $this->load();

                // we then add this latest message which we want to reply to
                $this->addMessage($threadOrEvent);

                // check to see if their may be some older messages to get
                // this might happen if we stop and start the webclient and miss the start
                // of a thread but get the next message event.
                if ($isChildMessage) {
                    if ($this->data['history'][0]['ts'] !== $threadId) {
                        // load the full thread which we do not have in the database
                        $this->updateFromSlack();
                    }
                }
            } else {
                $this->data['thread_id'] = $threadOrEvent;
                $this->data['channel'] = $channel;
                $this->load();
            }
        }
        $this->updateFromSlackIfRequired();
    }

    // should store all the bot data.
    public function addReply($replyState, $message = null)
    {
        if (isset($message)) {
            if (isset($this->data['history'])) {
                foreach ($this->data['history'] as &$event) {
                    if ($event['ts'] == $message['ts']) {
                        $event['slackbot_reply'] = $replyState;
                        $this->save();
                        return true;
                    }
                }
            }
        } else {
            $lastItem = &$this->data['history'][count($this->data['history'])-1];
            $lastItem['slackbot_reply'] = $replyState;
            $this->save();
            return true;
        }
        return false;
    }


    /**
     * Add a message OR replace existing message in the queue
     *
     * @param [type] $event
     * @return void
     */
    public function addMessage($event)
    {
        if (!isset($event['ts'])) {
            throw new \Exception("no ts attribute of array found");
        }
        // we only want main messages in our list.
        $isBotMessage = false;
        if (isset($event['subtype'])) {
            // note that if the bot message is a PARENT then there will not be a subtype but there will be a BOT_ID/BOT_PROFILE keys associated
            // with the event.
            if ($event['subtype'] === "bot_message") {
                // this is a reply to a message.  Which one?  Well the one with a time just bofore us.
                // or we could have started the dialog of course.
                $isBotMessage = true;
            } else {
                return false;
            }
        }
        if (isset($this->botId)) {
            if (isset($event['user']) && $event['user'] == $this->botId) {
                $isBotMessage = true;
            }
        }

        // mark each event with a local timestamp so that we know
        // when it was last updated
        $event['slackbot_local_timestamp'] = time();
        if (!isset($this->data['thread_id'])) {
            if (isset($event['thread_ts'])) {
                $this->data['thread_id'] = $event['thread_ts'];
            } else {
                $this->data['thread_id'] = $event['ts'];
            }
            $this->thread['history'][] = $event;
            $this->save();
            return true;
        }
        if (!isset($this->data['history'])) {
            $this->data['history'][] = $event;
            $this->save();
            return true;
        }
        // we want to ensure that it gets put in the right place (Should be at the end but
        // you never know!)
        $N = count($this->data['history']);
        if ($this->data['history'][0]['ts'] > $event['ts']) {
            if ($isBotMessage) {
                if (!isset($event['thread_ts']) || $event['thread_ts'] !== $event['ts']) {
                    throw new \Exception("Found a bot message that is not a parent before any of the other messages.");
                }
            }
            $this->data['history'] = array_merge([$event], $this->data['history']);
            return true;
        }
        for ($ii=$N-1; $ii >= 0; $ii--) {
            if ($this->data['history'][$ii]['ts'] < $event['ts']) {
                if ($ii == $N-1) {
                    if ($isBotMessage) {
                        $this->data['history'][count($this->data['history'])-1]['slackbot_reply']['event'] = $event;
                    } else {
                        $this->data['history'][] = $event;
                    }
                } else {
                    if ($isBotMessage) {
                        $this->data['history'][$ii]['slackbot_reply']['event'] = $event;
                    } else {
                        $this->data['history'] = array_merge(
                            array_slice($this->data['history'], 0, $ii+1),
                            [$event],
                            array_slice($this->data['history'], $ii+1)
                        );
                    }
                }
                $this->save();
                return true;
            } elseif ($this->data['history'][$ii]['ts'] == $event['ts']) {
                $this->data['history'][$ii] = $event;
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function getLastMessageWithReply()
    {
        if (!isset($this->data['history'])) {
            return null;
        }
        for ($ii=count($this->data['history'])-1; $ii >= 0; $ii++) {
            if (isset($this->data['history'][$ii]['slackbot_reply'])) {
                return $this->data['history'][$ii];
            }
        }
        return null;
    }

    public function hasMessagesWithoutReply()
    {
        foreach ($this->data['history'] as $m) {
            if (!isset($m['slackbot_reply'])) {
                return true;
            }
        }
        return false;
    }

    public function getNextMessageWithoutReply()
    {
        foreach ($this->data['history'] as $m) {
            if (!isset($m['slackbot_reply'])) {
                return $m;
            }
        }
        return false;
    }

    public function getLastMessage()
    {
        if (!isset($this->data['history'])) {
            return null;
        }
        return $this->data['history'][count($this->data['history'])-1];
    }

    public function id()
    {
        if (!isset($this->data) || !isset($this->data['thread_id'])) {
            return null;
        }
        return $this->data['thread_id'];
    }

    public function updateFromSlackIfRequired()
    {
        if (!isset($this->data['history'])) {
            return false;
        }
        $now = time();
        $N = count($this->data['history']);
        if ($N > 0) {
            $timestamp = $this->data['history'][$N-1]['slackbot_local_timestamp'];
            if ($now - $timestamp > SLACKBOT_CHECK_FOR_UPDATE_CONVERSATION_IN_SECONDS) {
                $this->updateFromSlack();
            }
        }
    }

    /**
     * Get the latest conversation from slack to see if we have missed anything
     * @return void
     */
    protected function updateFromSlack()
    {
        # load full thread from Slack and make sure we have everything
        # don't need to do this everytime though.
        if (!isset($this->data['thread_id'])) {
            throw new \Exception("thread_id should be set");
        }
        $threadId = $this->data['thread_id'];
        if (!isset($this->data['history'])) {
            return false;
        }
        $channel = $this->data['channel'];
        if (!isset($channel)) {
            throw new \Exception("Channel not found in first history item");
        }
        $cursor = null;
        $hasMore = true;
        $forcedBreak = 0;
        while ($hasMore) {
            $response = getConversationRepliesFromSlack($channel, $threadId, $cursor);
            var_dump($response);
            $hasMore = false;
            if (isset($response['has_more'])) {
                $hasMore = $response['has_more'];
            }
            $cursor = null;
            if (isset($response['response_metadata'])) {
                $cursor = $response['response_metadata']['next_cursor'];
            }
            if (isset($response['messages'])) {
                $messages = $response['messages'];
                foreach ($messages as $msg) {
                    $this->addMessage($msg);
                }
            }
            $forcedBreak++;
            if ($forcedBreak > 1000) {
                // force the break in case of infinite loop
                savelog("[ConversationState] ThreadId:".$this->data['thread_id']." Channel: ".$channel." Error: updateFromSlackForced break triggered");
                break;
            }
        }
        return true;
    }

    public function channel()
    {
        if (!isset($this->data['channel'])) {
            return null;
        }
        return $this->data['channel'];
    }

    public function load()
    {
        if (!isset($this->data) || !isset($this->data['thread_id'])) {
            return;
        }
        $options = ["typeMap" => ['root' => 'array', 'document' => 'array']];
        $collection = (new MongoDB\Client(null, [], $options))->slackbot->conversation_state;
        $conversationThread = $collection->findOne(["thread_id" => $this->id(), "channel" => $this->channel() ]);
        if (isset($conversationThread)) {
            $this->data = $conversationThread;
        }
    }

    public function save()
    {
        if (!isset($this->data['thread_id'])) {
            return false;
        }
        if (!isset($this->data['channel'])) {
            return false;
        }
        $collection = (new MongoDB\Client)->slackbot->conversation_state;
        $collection->replaceOne([ "thread_id" => $this->id(), "channel" => $this->channel() ], $this->data, [ "upsert" => true]);
        return true;
    }

    public function validate()
    {
        $lastEventTimestamp = 0;
        $lastUser = 0;
        foreach ($this->data['history'] as $item) {
            if ($item['ts'] < $lastEventTimestamp) {
                throw new \Exception("ordering of messages error detected");
            }
            if ($item['ts'] == $lastEventTimestamp && $item['user'] == $lastUser) {
                throw new \Exception("duplicate message with same timestamp and user detected");
            }
            $lastUser = $item['user'];
            $lastEventTimestamp = $item['ts'];
        }
        return true;
    }

    public function length()
    {
        if (!isset($this->data['history'])) {
            return 0;
        }
        return count($this->data['history']);
    }

    public function getMessage($index)
    {
        if (!isset($this->data['history'])) {
            return null;
        }
        if (!isset($this->data['history'][$index])) {
            return null;
        }
        return $this->data['history'][$index];
    }

    public function wasUserMentioned($user)
    {
        if (isset($this->data['history'])) {
            foreach ($this->data['history'] as $message) {
                if (isset($message['blocks'])) {
                    $blocks = $message['blocks'];
                    foreach ($blocks as $block) {
                        $usersArray = checkElementsForUserID($block);
                        $usersArray = array_unique($usersArray);
                        savelog("::wasUseMentioned::");
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
}


class MockConversationState extends ConversationState
{
    public function setData($data)
    {
        $this->data = $data;
    }
    public function getData()
    {
        return $this->data;
    }
}
