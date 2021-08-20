<?php

/**
 * Dialogues hold a more structured expected conversation.
 *
 * By using the keywords 'today' for send_date and 'now' for send_time you can send a message every time the
 * post_message is run.  Do not do this otherwise though otherwise it will launch the initial message every
 * minute of everyday.
 *
 */

class Dialogue
{
    private $data = null;

    public function loadFromFile($file)
    {
        $contents = file_get_contents($file);
        $jsondata = json_decode($contents, true);
        $this->data = $jsondata;
    }

    // any variables should have been filled in by now so we are just string matching
    public function match($slackMessage)
    {
        savelog("Dialogue::match");
        savelog(json_encode($slackMessage));
        # we can restrict this dialogue to only direct messages by using 'im' as the message_type
        # the first message posted by bot will not show up as an im message.
        // if (isset($this->data['message_type'])) {
        //     if (isset($slackMessage['channel_type'])) {
        //         if ($slackMessage['channel_type'] != $this->data['message_type']) {
        //             savelog("Dialogue::match::failed channel_type check");

        //             return false;
        //         }
        //     } else {
        //         savelog("Dialogue::match::failed channel_type is null");

        //         return false;
        //     }
        // }


        // need to match both the string AND the date it was sent on.
        // as we are only expecting to initiate 1 message per day, then that should be
        // ok.
        $plainText = $slackMessage['text'];
        $timepoint = date("Y-m-d", $slackMessage['ts']/1000);
        savelog("Dialogue::match::check date ".$timepoint);

        if ($this->matchDate($timepoint)) {
            return true;
            # in fact for the experiment we are just sending 1 per day so
            # we just need to check the date (for now).
            // if (strtolower($this->data['dialog'][0]) == strtolower($plainText)) {
            //     return true;
            // }
        }
        return false;
    }

    public function getInitialText()
    {
        if (!isset($this->data['dialog'])) {
            return null;
        }
        if (!isset($this->data['dialog'][0])) {
            return null;
        }
        if (!isset($this->data['dialog'][0]['text'])) {
            return null;
        }
        return $this->data['dialog'][0]['text'];
    }

    public function matchDate($dateAsYYYYMMDD)
    {
        if (!isset($this->data['send_date'])) {
            return false;
        }
        if ($this->data['send_date'] == "today") {
            return true;
        }
        if ($this->data['send_date'] == $dateAsYYYYMMDD) {
            return true;
        }
        return false;
    }

    public function matchTime($timeAsHHMMSS, $allowMatchingTimeOfValueNow=false)
    {
        if (!isset($this->data['send_time'])) {
            return false;
        }
        if ($this->data['send_time'] == "now" && $allowMatchingTimeOfValueNow) {
            return true;
        }
        if ($this->data['send_time'].":00" == $timeAsHHMMSS) {
            return true;
        }
        return false;
    }

    # @param responseState      is the array from the bot that has the clauses and sentiment in
    # @return mixed             null means continue with intent, false means don't reply, string if to reply to human
    public function nextResponse($conversationState, $responseState)
    {
        // once we know the dialogue matches the first item in this conversation we then need to work out which index we are on.
        // the conversation should go something like this:
        //      [0] bot
        //      [1] human (slackbot_reply)
        //      [2] human (slackbot_reply)
        //      ... etc
        $lastMessage = $conversationState->getLastMessage();
        $msgCount = $conversationState->length();
        if (isset($lastMessage['slackbot_reply'])) {
            // we are waiting for a human reply so there should not be anything to do.
            savelog("[ERROR] Awaiting human reply to last message - this should not trigger.");
        } else {
            if ($msgCount >= count($this->data['dialog'])) {
                # if for whatever reason the user tries to prompt Alice afterwards then
                # she should not reply as the last message will be a goodbye one.
                savelog("user responded for longer than dialog, ignoring user input");
                return false;
            }
            $dialogIndex = $msgCount - 1;
            if ($dialogIndex <= 0) {
                savelog("[ERROR] The dialog index is ".$dialogIndex.", this should not occurs.");
            } else {
                $lastReply = $this->data['dialog'][$dialogIndex-1];
                $nextReply = $this->data['dialog'][$dialogIndex];
                $joinOptions = $lastReply['joins'];
                $joinText = $this->getJoinText(
                    $joinOptions,
                    $lastMessage,
                    $responseState['overall_sentiment_value'],
                    $responseState['overall_emoji_sentiment_value']
                );
                savelog("join text: ".$joinText);
                savelog("next text: ".$nextReply['text']);
                $replyText = $joinText." ".$nextReply['text'];
                savelog("Dialog Text: ".$replyText);
                return ($replyText);
            }
        }
        return false;
    }

    private function getJoinText($joinOption, $lastMessage, $textSentiment, $emojiSentiment)
    {
        $sentiment = ($textSentiment + $emojiSentiment) / 2.0;
        savelog("join sentiment: ".$sentiment);

        $possibleJoinText = [];
        $defaultJoinText = "";
        foreach ($joinOption as $join) {
            if ($join['condition'] == "positive-sentiment") {
                if ($sentiment > 2) {
                    $possibleJoinText[] = $join['text'];
                }
            }
            if ($join['condition'] == "negative-sentiment") {
                if ($sentiment < 2) {
                    $possibleJoinText[] = $join['text'];
                }
            }
            if ($join['condition'] == "default") {
                $defaultJoinText = $join['text'];
            }
        }
        if (count($possibleJoinText) === 0) {
            return $defaultJoinText;
        }
        return $possibleJoinText[0];
    }
}
