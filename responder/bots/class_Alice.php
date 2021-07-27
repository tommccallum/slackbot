<?php

// TODO shift this to separate file
function compareIntentMatches($a, $b) {
    return $a['match'][0]['start_index']  - $b['match'][0]['start_index'];
}

# Just sends back a test response when used
class Alice extends Bot
{
    public $mode = "Alice";
    private $sentimentModelPath = __DIR__."/../../models/sentiment_model.json";
    private $emojiSentimentModelPath = __DIR__."/../data/emoticons_sentiment.txt";

    protected function respond($question) {
        $response = "Hi, this is Alice.  I don't take orders, but you can chat with me by using @Alice.";
        return $response;
    }


    protected function onMessage($app)
    {
        savelog("Alice::onMessage");
        
        $message = $app->event;
        $resultArray = walk_message_blocks($message, "getTextBlocks");
        $text = collapseTextAndEmojiBlocksIntoString($resultArray);
        $clauses = splitStringIntoClauses($text);
        $clausesAsString = outputClausesAsString($clauses);

        $sentimentValue = 2;
        $emojiSentimentValue = 2;
        foreach( $clauses as $clause ) {
            if ( $clause['type'] == "EXCLAMATION" ) {
                $sentiment = new SentimentAnalyser();
                $sentiment->loadModel($this->sentimentModelPath);
                $sentimentValue = $sentiment->classifyLexemes($clause['lexemes']);
                $emojiClassifier = new EmojiSentimentAnalyser();
                $emojiClassifier->loadModel($this->emojiSentimentModelPath);
                $emojiSentimentValue = $emojiClassifier->classifyLexemes($clause['lexemes']);
                savelog("Sentiment value of string: ".$sentimentValue);
                savelog("Sentiment value of emoji: ".$emojiSentimentValue);
            } else {
                savelog("Ignoring clause for sentiment.");
            }
        }

        if ( $emojiSentimentValue > 2 || $sentimentValue > 2 ) {
            sendSlackReaction($app, "thumbsup");
        }
        if ($emojiSentimentValue < 2 && $sentimentValue < 2) {
            sendSlackReaction($app, "cry");
        }
        
        $user = whoami($app->event['user']);
        $resultArray = walk_message_blocks($message, "getUserBlocks");
        #$userIds = collapseUserBlocksIntoArray($resultArray);

        # a piece of text can have multiple intents e.g. a greeting and a request
        # we check if they match and then if they do we add the match to our array
        # we can then sort the array by the starting location of the match.
        $response = "";
        foreach ($clauses as $clause) { // we want to respond to each part of the users message

            $matchingIntents = array();
            foreach ($this->intents as $intent) {
                $match = $intent->isLike($clause);
                if (isset($match)) {
                    $matchingIntents[] = $match;
                }
            }
            // var_dump($matchingIntents);
            usort($matchingIntents, "compareIntentMatches");
            
            // var_dump($matchingIntents);

            # we will respond in the same order as the intents
            # we then generate the appropriate replies
            $you = createSlackUserProfile($user['id']);
            $me = new Me();
            $partsOfDay = new PartOfDay();
            $replacements = [ "you" => $you, "me" => $me, "part_of_day" => $partsOfDay ];
            foreach ($matchingIntents as $intent) {
                $replyText = $intent['action']($intent);

                $replyText = replaceTags($replyText, $replacements);

                # fit the pieces of sentence together
                if ( preg_match("/[.?!]\s*$/", $response ) ) {
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
        if (preg_match("/[.?!]\s*$/", $response) === false) {
            $response .= ".";
        }

        return $response;
    }

    protected function onSomeoneHasJoinedTheChannel($app) {
        return "Welcome <@".$app->event['user'].">";
    }

}