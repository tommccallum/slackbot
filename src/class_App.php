<?php

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
    public $botSelectionName = "Eliza";

    function __construct($inputArguments) {
        try {
            $this->fromInternet($inputArguments);
        } catch( Exception $ex) {
            $this->fromConsole($inputArguments);
        }
    }

    function fromInternet($inputArguments) {
        if ( isset($inputArguments['command']) ) {
            $this->sendToSlack = true;
            $this->command = $inputArguments['command'];
            if (isset($inputArguments['text'])) {
                $this->text = $inputArguments['text'];
            }
            if (isset($inputArguments['token'])) {
                $this->token = $inputArguments['token'];
            }
            if (isset($inputArguments['channel_id'])) {
                $this->channelId = $inputArguments['channel_id'];
            }
            if (isset($inputArguments['user_name'])) {
                $this->userName = $inputArguments['user_name'];
            }
            if ( isset($inputArguments['sendToSlack'])) {
                $this->sendToSlack = $inputArguments['sendToSlack'];
            }
        } else {
            throw new Exception("No command specified");
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