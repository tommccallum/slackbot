<?php

// TODO Shift this to separate file and deduplicate with Alice bot.
function compareIntentMatches($a, $b) {
    return $a['match'][0]['start_index']  - $b['match'][0]['start_index'];
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
        $text = collapseTextAndEmojiBlocksIntoString($resultArray);
        $response .= "\n\n".$text;

        $clauses = splitStringIntoClauses($text);
        var_dump($clauses);
        $clausesAsString = outputClausesAsString($clauses);
        $response .= "\n\n".$clausesAsString;

        $response .= "\n\n";
        foreach( $clauses as $clause ) {
            if ( $clause['type'] == "EXCLAMATION" ) {
                $sentiment = new SentimentAnalyser();
                $sentiment->loadModel($this->sentimentModelPath);
                $sentimentValue = $sentiment->classifyLexemes($clause['lexemes']);
                $emojiClassifier = new EmojiSentimentAnalyser();
                $emojiClassifier->loadModel($this->emojiSentimentModelPath);
                $emojiSentimentValue = $emojiClassifier->classifyLexemes($clause['lexemes']);
                $response .= "\nSentiment: ".$sentimentValue." Emoji Sentiment: ".$emojiSentimentValue;
            } else {
                $response .= "\nIgnoring clause.";
            }
        }
        
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

                # HACK move this out to its own function
                $replyText = replaceTags($replyText, $replacements);
                
                $response .= "\n\n".$intent['name']." generated: ".$replyText;
            }
        }
        
        return $response;
    }

    protected function onSomeoneHasJoinedTheChannel($app) {
        return "Welcome <@".$app->event['user'].">";
    }

}