<?php

function compareIntentMatches($a, $b) {
    return $a['start_position']  - $b['start_position'];
}

# Just sends back a test response when used
class ResponderBot extends Bot
{
    public $mode = "Responder Bot";
    private $sentimentModelPath = __DIR__."/../../models/sentiment_model.json";
    private $emojiSentimentModelPath = __DIR__."/../data/emoticons_sentiment.txt";

    protected function respond($question) {
        $response = "Hi, this is a test response at ".date("H:m")." on ".date("l jS F Y").".";
        return $response;
    }


    protected function onMessage($app)
    {
        savelog("TestBot::onMessage");
        sendSlackReaction($app, "thumbsup");
        $response = "Hi, this is a test response at ".date("H:m")." on ".date("l jS F Y").".";


        $message = $app->event;
        $resultArray = walk_message_blocks($message, "getTextBlocks");
        $text = collapseTextBlocksIntoString($resultArray);
        $resultArray = walk_message_blocks($message, "getEmojiBlocks");
        $emojis = collapseEmojiBlocksIntoArray($resultArray);
        $sentiment = new SentimentAnalyser();
        $sentiment->loadModel($this->sentimentModelPath);
        $sentimentValue = $sentiment->classify($text);
        $emojiClassifier = new EmojiSentimentAnalyser();
        $emojiClassifier->loadModel($this->emojiSentimentModelPath);
        $emojiSentimentValue = $emojiClassifier->classify($emojis);

        $response .= "\n\nSentiment: ".$sentimentValue." Emoji Sentiment: ".$emojiSentimentValue;

        $response .= "\n\n".$text;
        
        $user = whoami($app->event['user']);
        // var_dump($user);
        $firstname = $user['profile']['first_name'];
        $response .= "\n\nPerson I am talking to is ".$firstname." (".$user['profile']['display_name'].")";

        $resultArray = walk_message_blocks($message, "getUserBlocks");
        $userIds = collapseUserBlocksIntoArray($resultArray);
        $userProfiles = whoami($userIds);
        $userText = "";
        foreach( $userProfiles as $userProfile ) {
            if ( $userText !== "" ) {
                $userText .= ",";
            }
            if ( isset($userProfile['is_bot']) ) {
                $userText .= "Bot (".$userProfile['profile']['real_name'].")";
            } else {
                $userText .= $userProfile['profile']['first_name']." (".$userProfile['profile']['display_name'].")";
            }
        }
        $response .= "\n\nMentioned: ".$userText;

        $response .= "\n\nIntents: ".count($this->intents);

        # a piece of text can have multiple intents e.g. a greeting and a request
        # we check if they match and then if they do we add the match to our array
        # we can then sort the array by the starting location of the match.
        $matchingIntents = array();
        foreach( $this->intents as $intent ) {
            $match = $intent->isLike($text);
            if ( isset($match) ) {
                $matchingIntents[] = $match;
            }
        }
        usort($matchingIntents, "compareIntentMatches");

        // var_dump($matchingIntents);

        # we will respond in the same order as the intents
        # we then generate the appropriate replies

        foreach( $matchingIntents as $intent ) {
            $replyText = $intent['action']($intent);

            # HACK move this out to its own function
            $replyText = preg_replace("/%you.firstname%/", $firstname, $replyText);

            $response .= "\n\n".$intent['name']." generated: ".$replyText;
        }

        # can we find the topic of the conversation by analysing the sentence?
        $words = splitStringIntoLexemes($text);
        $result = new LexicalAnalysis();
        $result->inferPartsOfSpeechArray($words);
        $sentence = $result->getTaggedText();

        $response .= "\n\n$sentence";

        return $response;
    }

    protected function onSomeoneHasJoinedTheChannel($app) {
        return "Welcome <@".$app->event['user'].">";
    }

}