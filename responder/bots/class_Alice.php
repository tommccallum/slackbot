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
            if (isset($reply)) {
                $replies[] = $reply;
            }
        }
        return $replies;
    }

    private function replyToMessage()
    {
        $message = $this->conversationState->getNextMessageWithoutReply();
        if (!isset($message)) {
            return null;
        }

        $responseState = [];
        $resultArray = walk_message_blocks($message, "getTextBlocks");
        $text = collapseTextAndEmojiBlocksIntoString($resultArray);
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

        # a piece of text can have multiple intents e.g. a greeting and a request
        # we check if they match and then if they do we add the match to our array
        # we can then sort the array by the starting location of the match.
        $response = "";
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
            return false;
        }
        if ($event['event']['type'] != "message") {
            return false;
        }
        $message = $event['event'];
        if ($message['channel_type'] == "im") {
            # IF this is a private IM conversation then we reply to everything as we are the only other
            #   user in the chat.
            return true;
        }
        if ($message['subtype'] == "channel_join") {
            # Always say welcome to anyone joining the public groups we are in
            return true;
        }
        if ($message['subtype'] == "message_changed") {
            # never respond if someone updates their message
            # we are not even going to update our history as otherwise all our responses will be wrong
            # and not sure of a use case where we care.
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
        if (!isset($this->conversation)) {
            return false;
        }
        return $this->conversation->wasUserMentioned($this->slackUserId);
    }

    public function didThisBotStartTheConversation()
    {
        if (!isset($this->conversation)) {
            return false;
        }
        $firstMessage = $this->conversation->getMessage(0);
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
