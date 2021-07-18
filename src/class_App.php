<?php

DEFINE("TYPE_UNKNOWN", 0);
DEFINE("TYPE_CHALLENGE", 1);
DEFINE("TYPE_COMMAND", 2);
DEFINE("TYPE_EVENT", 3);
DEFINE("TYPE_SELF", 4);

class App 
{
    public $sendToSlack = false;					// flag for if we are being called from slack or from command line
    public $command = null;
    public $channelId = null;
    public $text = null;
    public $token = null;
    public $userName = null;
    public $language = null;
    public $languageName = null;
    public $botSelectionName = "Test";
    public $responseUrl = null;
    public $teamId = null;
    public $teamDomain = null;
    public $channelName = null;
    public $apiAppId = null;
    public $isEnterpriseInstall = false;
    public $triggerId = null;
    public $challenge = null;
    public $type = null;
    public $jsonRequest = null;
    public $event = null;
    public $authorizations = null;
    public $eventContext = null;

    function __construct($inputArguments) {
        try {
            $this->fromInternet($inputArguments);
        } catch( Exception $ex) {
            $this->fromConsole($inputArguments);
        }
    }

    function getChannelId() {
        if ( isset($this->event['channel']) ) {
            return ($this->event['channel']);
        }
        return $this->channelId;
    }

    private function type() {
        if ( isset($this->command) ) {
            return TYPE_COMMAND;
        } else if ( isset($this->event) ) {
            return TYPE_EVENT;
        } else if ( isset($this->challenge) ) {
            return TYPE_CHALLENGE;
        }
        return TYPE_UNKNOWN;
    }

    public function isChallenge() {
        return $this->type() == TYPE_CHALLENGE;
    }

    public function isCommand() {
        return $this->type() == TYPE_COMMAND;
    }

    public function isEvent() {
        return $this->type() == TYPE_EVENT;
    }

    public function isSelf() {
        return $this->type() == TYPE_EVENT && $this->event['subtype'] === "bot_message" && $this->event['bot_id'] === BOT_ID;
    }

    function fromInternet($inputArguments) {
        $this->jsonRequest = $inputArguments;
        $this->sendToSlack = true;
        
        if ( isset($inputArguments['sendToSlack'])) {
            $this->sendToSlack = $inputArguments['sendToSlack'];
        }
        if (isset($inputArguments['token'])) {
            $this->token = $inputArguments['token'];
        }
        if (isset($inputArguments['team_id'])) {
            $this->team_id = $inputArguments['team_id'];
        }
        if (isset($inputArguments['api_app_id'])) {
            $this->api_app_id = $inputArguments['api_app_id'];
        }
        
        # command and event messages are different
        if ( isset($inputArguments['challenge'])) {
            # challenge message
            $this->challenge = $inputArguments['challenge'];
            if ( isset($inputArguments['type'])) {
                $this->type = $inputArguments['type'];
            }
        } else if (isset($inputArguments['command'])) { // slack command initiated with /alice
            // {"token":"YRa10rG6JrFoGpB3n8fBY3NT",
            //     "team_id":"TUPQR1UBH",
            //     "team_domain":"asd-at-uhi",
            //     "channel_id":"C023JCGLMGB",
            //     "channel_name":"virtual-assistant-dev",
            //     "user_id":"UUNQNAB24",
            //     "user_name":"mo04tm",
            //     "command":"\/alice",
            //     "text":"this is a command",
            //     "api_app_id":"A01CBLUJU3U",
            //     "is_enterprise_install":"false",
            //     "response_url":"https:\/\/hooks.slack.com\/commands\/TUPQR1UBH\/2282447058613\/au9d6swBVIcEq2LxK0S5ShDr",
            //     "trigger_id":"2298124972993.975841062391.eabc08b133d336494b3e4eb2c341a96e"}

            $this->command = $inputArguments['command'];
            if (isset($inputArguments['text'])) {
                $this->text = $inputArguments['text'];
            }
            if (isset($inputArguments['channel_id'])) {
                $this->channelId = $inputArguments['channel_id'];
            }
            if ( isset($inputArguments['channel_name'])) {
                $this->channelName = $inputArguments['channel_name'];
            }
            if (isset($inputArguments['user_name'])) {
                $this->userName = $inputArguments['user_name'];
            }
            if (isset($inputArguments['user_id'])) {
                $this->userId = $inputArguments['user_id'];
            }
            if ( isset($inputArguments['is_enterprise_install'])) {
                $this->isEnterpriseInstall = $inputArguments['is_enterprise_install'];
            }
            if ( isset($inputArguments['response_url'])) {
                $this->responseUrl = $inputArguments['response_url'];
            }
            if ( isset($inputArguments['trigger_id'])) {
                $this->triggerId = $inputArguments['trigger_id'];
            }
            if ( isset($inputArguments['team_domain'])) {
                $this->teamDomain = $inputArguments['team_domain'];
            }
            
            
        } else if ( isset($inputArguments['event'])) { // slack event initiated by @alice for instance
            // {
            //     "token":"YRa10rG6JrFoGpB3n8fBY3NT",
            //     "team_id":"TUPQR1UBH",
            //     "api_app_id":"A01CBLUJU3U",
            //     "event":{"client_msg_id":"9fa00442-8d69-4be8-afc5-87502a732303",
            //         "type":"message",
            //         "text":"hello again <@U01CHRXLSUC>",
            //         "user":"UUNQNAB24",
            //         "ts":"1626531397.000500",
            //         "team":"TUPQR1UBH",
            //         "blocks":[
            //             {
            //                 "type":"rich_text",
            //                 "block_id":"eFs",
            //                 "elements":[{
            //                     "type":"rich_text_section",
            //                     "elements":[
            //                         {
            //                             "type":"text",
            //                             "text":"hello again "
            //                         },
            //                         {
            //                             "type":"user",
            //                             "user_id":"U01CHRXLSUC"
            //                         }
            //                     ]
            //                 }]
            //             }
            //         ],
            //         "channel":"C023JCGLMGB",
            //         "event_ts":"1626531397.000500",
            //         "channel_type":"channel"
            //     },
            //     "type":"event_callback",
            //     "event_id":"Ev028KKBV4R2",
            //     "event_time":1626531397,
            //     "authorizations":[{"enterprise_id":null,"team_id":"TUPQR1UBH","user_id":"U01CHRXLSUC","is_bot":true,"is_enterprise_install":false}],
            //     "is_ext_shared_channel":false,
            //     "event_context":"3-message-TUPQR1UBH-A01CBLUJU3U-C023JCGLMGB"
            // }
            //
            // There are also bot messages:
            // 
            // {"token":"YRa10rG6JrFoGpB3n8fBY3NT","team_id":"TUPQR1UBH","api_app_id":"A01CBLUJU3U",
            // "event":{"type":"message","subtype":"bot_message","text":"Hello World!","ts":"1626535205.001200",
            // "bot_id":"B025930SRHP","channel":"C023JCGLMGB","event_ts":"1626535205.001200","channel_type":"channel"},
            // "type":"event_callback","event_id":"Ev028AE81MRB","event_time":1626535205,
            // "authorizations":[{"enterprise_id":null,"team_id":"TUPQR1UBH","user_id":"U01CHRXLSUC","is_bot":true,"is_enterprise_install":false}],
            // "is_ext_shared_channel":false,"event_context":"3-message-TUPQR1UBH-A01CBLUJU3U-C023JCGLMGB"}



            if ( isset($inputArguments['event']) ) {
                $this->event = $inputArguments['event'];
                $this->channelId = $this->event['channel'];
                $this->type = $this->event['type'];
            }
            if ( isset($inputArguments['is_ext_shared_channel'])) {
                $this->isExtSharedChannel = $inputArguments['is_ext_shared_channel'];
            }
            if ( isset($inputArguments['event_context'])) {
                $this->eventContext = $inputArguments['event_context'];
            }
            if ( isset($inputArguments['type'])) {
                $this->type = $inputArguments['type'];
            }
            if ( isset($inputArguments['authorizations'])) {
                $this->authorizations = $inputArguments['authorizations'];
            }
        }
    }

    function fromConsole($inputArguments) {
        array_shift($inputArguments); // get rid of application name
        if ( count($inputArguments) == 0 ) {
            throw new Exception("No arguments or text received, nothing to do.");
        }
        if( count($inputArguments) == 1 ) {
            $this->text = $inputArguments[0];
            return;
        }
        $ii = 0;
        while ( $ii < count($inputArguments)) {
            if ( $inputArguments[$ii] == '-t' ) {
                $this->text = $inputArguments[++$ii];
            }
            $ii++;
        }
    }
}