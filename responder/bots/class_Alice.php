<?php

# Just sends back a test response when used
class Alice extends Bot
{
    public $mode = "Alice";
    private $sentimentModelPath = __DIR__."/../../models/sentiment_model.json";
    private $emojiSentimentModelPath = __DIR__."/../data/emoticons_sentiment.txt";
    private $conversationState = null;
    private $slackUserId = null;

    public function __construct(&$app)
    {
        parent::__construct($app);
        $this->slackUserId = getAliceId();
    }

    protected function respond($question)
    {
        $response = "Hi, this is Alice.  I don't take orders, but you can chat with me by using @Alice.";
        return $response;
    }


    protected function onMessage()
    {
        savelog("Alice::onMessage");
        if (!isset($this->conversationState)) {
            # This should be loaded by now as the first function should have called
            # shouldBotReplyToEvent.
            # This does happen during testing though.
            savelog("onMessage::Loading conversation state");
            $this->conversationState = new ConversationState($this->app->event, $this->slackUserId);
        }

        $replies = [];
        while ($this->conversationState->hasMessagesWithoutReply()) {
            $reply = $this->replyToMessage();
            if (isset($reply) && strlen($reply) > 0) {
                $replies[] = $reply;
            }
        }
        return $replies;
    }

    private function replyToMessage()
    {
        $isBotInitiatedThread = $this->conversationState->isBotInitiatedThread();
        $message = $this->conversationState->getNextMessageWithoutReply();
        if (!isset($message)) {
            return null;
        }
        savelog("Replying to message: ".$message['ts']);

        if (isset($message['user'])) {
            if ($this->slackUserId == $message["user"]) {
                ## don't reply to ourselves!
                $responseState['text'] = null;
                $responseState['reason'] = "Ignored, as reply to self";
                $this->conversationState->addReply($responseState, $message);
                savelog("Ignoring own bot message");
                return null;
            }
        }

        $responseState = [];
        $resultArray = walk_message_blocks($message, "getTextBlocks");
        $text = collapseTextAndEmojiBlocksIntoString($resultArray);
        if (strlen($text) === 0) {
            # this happens if we are parsing a message a bot posted, these do not seem to have the same
            # structure of message.
            if (isset($message['text'])) {
                $text = $message['text'];
            }
        }
        savelog("User provided text: ".$text);
        $clauses = splitStringIntoClauses($text);
        $responseState['clauses'] = $clauses;

        $sentimentValue = 2;
        $emojiSentimentValue = 2;
        foreach ($clauses as $index => $clause) {
            if ($clause['type'] == "EXCLAMATION") {
                $sentiment = new SentimentAnalyser();
                $sentiment->loadModel($this->sentimentModelPath);
                $sentimentValue = $sentiment->classifyLexemes($clause['lexemes']);
                $emojiClassifier = new EmojiSentimentAnalyser();
                $emojiClassifier->loadModel($this->emojiSentimentModelPath);
                $emojiSentimentValue = $emojiClassifier->classifyLexemes($clause['lexemes']);

                // save sentiment for each clause
                $responseState['clauses'][$index]['sentiment_value'] = $sentimentValue;
                $responseState['clauses'][$index]['emoji_sentiment_value'] = $emojiSentimentValue;

                savelog("Sentiment value of string: ".$sentimentValue);
                savelog("Sentiment value of emoji: ".$emojiSentimentValue);
            } else {
                savelog("Ignoring clause for sentiment.");
            }
        }

        // save sentiment for overall
        $responseState['overall_sentiment_value'] = $sentimentValue;
        $responseState['overall_emoji_sentiment_value'] = $emojiSentimentValue;

        if ($emojiSentimentValue > 2 || $sentimentValue > 2) {
            sendSlackReaction($this->app, "thumbsup");
        }
        if ($emojiSentimentValue < 2 && $sentimentValue < 2) {
            sendSlackReaction($this->app, "cry");
        }
        
        $user = whoami($this->app->event['user']);
        $resultArray = walk_message_blocks($message, "getUserBlocks");
        #$userIds = collapseUserBlocksIntoArray($resultArray);

        $generateResponseUsingIntentions = true;
        $response = "";
        if ($isBotInitiatedThread) {
            savelog("Detected bot initiated thread");
            // in this case there may be an expected dialog going on.
            $dialogCollection = new DialogueCollection();
            $dialogCollection->loadFromDirectory();
            $selectedDialog = $dialogCollection->matchConversation($this->conversationState);
            if (count($selectedDialog) == 0) {
                // continue as if no dialog existed.
                savelog("No matching dialogue found.");
            } else {
                savelog("Found matching dialogs (".count($selectedDialog)." dialog objects)");
                $replyText = $selectedDialog[0]->nextResponse($this->conversationState, $responseState);
                if (isset($replyText) && is_string($replyText)) {
                    $you = createSlackUserProfile($user['id']);
                    $me = new Me();
                    $partsOfDay = new PartOfDay();
                    $replacements = [ "you" => $you, "me" => $me, "part_of_day" => $partsOfDay ];
                    $response = replaceTags($replyText, $replacements);
                    $generateResponseUsingIntentions = false;
                } elseif (isset($replyText) && $replyText === false) {
                    # we don't want to give the user a reply
                    # so we turn off intent based replies and set
                    # the response to empty.
                    $response = "";
                    $generateResponseUsingIntentions = false;
                } else {
                    savelog("Dialog exited with null, so going to intent based response");
                }
            }
        }

        if ($generateResponseUsingIntentions) {
            # a piece of text can have multiple intents e.g. a greeting and a request
            # we check if they match and then if they do we add the match to our array
            # we can then sort the array by the starting location of the match.
            foreach ($clauses as $index => $clause) { // we want to respond to each part of the users message

                // We split forming a reply into 2 parts:
                // 1. generating a set of specification which say what we have available to reply with
                // 2. the second does the actual replying.  This means we have the option to go back and try the "2nd"
                //      option if we get asked to "try again" or something similar.
                $matchingIntents = array();
                foreach ($this->intents as $intent) {
                    $match = $intent->isLike($clause);
                    if (isset($match)) {
                        $matchingIntents[] = $match;
                    }
                }
                // var_dump($matchingIntents);
                usort($matchingIntents, "compareIntentMatches");
                
                // save out intents so if need be we could write a proc to retry and then take a different option.
                $responseState['clauses'][$index]['matchingIntents'] = $matchingIntents;

                // var_dump($matchingIntents);

                # we will respond in the same order as the intents
                # we then generate the appropriate replies
                $you = createSlackUserProfile($user['id']);
                $me = new Me();
                $partsOfDay = new PartOfDay();
                $replacements = [ "you" => $you, "me" => $me, "part_of_day" => $partsOfDay ];
                foreach ($matchingIntents as &$match) {
                    $replyText = null;
                    foreach ($this->intents as $intent) {
                        if ($intent->name() == $match['intent_name']) {
                            $replyText = $intent->getReply($match);
                            break;
                        }
                    }
                    if (isset($replyText)) {
                        $replyText = replaceTags($replyText, $replacements);

                        # fit the pieces of sentence together
                        if (preg_match("/[.?!]\s*$/", $response)) {
                            // we need to start a new sentence.
                            if ($response == "") {
                                $response .= ucFirst($replyText);
                            } else {
                                $response .= " ".$replyText;
                            }
                        } else {
                            if ($response == "") {
                                $response = ucFirst($replyText);
                            } else {
                                $response .= ", ".$replyText;
                            }
                        }
                    }
                }
            }
            if (preg_match("/[.?!]\s*$/", $response) === false) {
                $response .= ".";
            }
        }

        savelog("Generated response (if empty user won't see this): ".$response);
        $responseState['text'] = $response;
        $this->conversationState->addReply($responseState, $message);

        if (strlen($response) == 0) {
            return null;
        }
        return $response;
    }

    protected function onSomeoneHasJoinedTheChannel()
    {
        return "Welcome <@".$this->app->event['user'].">";
    }

    public function shouldBotReplyToEvent()
    {
        $event = $this->app->jsonRequest;
        if (!isset($event['event'])) {
            savelog("shouldBotReplyToEvent::no event item in array");

            return false;
        }
        if ($event['event']['type'] != "message") {
            savelog("shouldBotReplyToEvent::event type is not message");
            return false;
        }
        $message = $event['event'];
        if (isset($message['channel_type']) && $message['channel_type'] == "im") {
            # IF this is a private IM conversation then we reply to everything as we are the only other
            #   user in the chat.
            return true;
        }
        if (isset($message['subtype']) && $message['subtype'] == "channel_join") {
            # Always say welcome to anyone joining the public groups we are in
            return true;
        }
        if (isset($message['subtype']) && $message['subtype'] == "message_changed") {
            # never respond if someone updates their message
            # we are not even going to update our history as otherwise all our responses will be wrong
            # and not sure of a use case where we care.
            savelog("shouldBotReplyToEvent::event subtype is message_changed");
            return false;
        }
        if (!isset($this->conversationState)) {
            # load the existing conversation either from database or Slack
            $this->conversationState = new ConversationState($message, $this->slackUserId);
        }
        if ($this->didThisBotStartTheConversation()) {
            return true;
        }
        if ($this->didThisBotGetMentionedInTheConversation()) {
            return true;
        }
        savelog("shouldBotReplyToEvent::exiting at end of function");
        return false;
    }

    


    /**
     * Look for any "mentions" of Alice in the entire thread
     *
     * @param [type] $conversation
     * @return void
     */
    public function didThisBotGetMentionedInTheConversation()
    {
        savelog("Checking for slack user id: ".$this->slackUserId);
        if (!isset($this->conversationState)) {
            savelog("exiting early due to no conversationState being set");
            return false;
        }
        return $this->conversationState->wasUserMentioned($this->slackUserId);
    }

    public function didThisBotStartTheConversation()
    {
        if (!isset($this->conversationState)) {
            return false;
        }
        $firstMessage = $this->conversationState->getMessage(0);
        if (!isset($firstMessage)) {
            return false;
        }
        if (!isset($firstMessage['user'])) {
            savelog(json_encode($firstMessage));
            throw new \Exception("expected user key in message array");
        }
        $initiatingUser = $firstMessage['user'];
        if ($initiatingUser == $this->slackUserId) {
            return true;
        }
        return false;
    }
}
