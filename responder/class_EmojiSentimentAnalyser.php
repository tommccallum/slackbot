<?php

// like the word sentiment we have a value between 0 and 4.
// TODO look at more of a wider range of emotional states rather than positive or negative as that is 
//      not super useful.

class EmojiSentimentAnalyser
{
    private $emojiSentimentMap = [];
    private $defaultEmojiValue = 2;

    public function classify($emojis) {
        $totalSentiment = 0.0;
        $numberOfEmojis = 0.0;
        if ( is_array($emojis) ) {
            $numberOfEmojis = count($emojis);
            foreach( $emojis as $emoji ) {
                if ( isset($this->emojiSentimentMap[$emoji]) ) {
                    $totalSentiment += $this->emojiSentimentMap[$emoji];
                } else {
                    $totalSentiment += $this->defaultEmojiValue;
                }
            }
        } else {
            if (isset($this->emojiSentimentMap[$emojis])) {
                $totalSentiment += $this->emojiSentimentMap[$emojis];
                $numberOfEmojis++;
            } else {
                $totalSentiment += $this->defaultEmojiValue;
                $numberOfEmojis++;
            }
        }
        if ( $numberOfEmojis === 0 ) {
            $averageSentiment = 2.0;
        } else {
            $averageSentiment = $totalSentiment / $numberOfEmojis;
        }
        return $averageSentiment;
    }

    public function loadModel($path) {
        $contents = array_map("str_getcsv",file($path));
        foreach( $contents as $row ) {
            $this->emojiSentimentMap[$row[0]] = $row[1];
        }
    }
}