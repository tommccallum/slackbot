<?php

class App 
{
    public $isSlack = 1;					// flag for if we are being called from slack or from command line
    public $command = null;
    public $channelId = null;
    public $text = null;
    public $token = null;
    public $userName = null;
    public $language = null;
    public $languageName = null;
    public $botSelectionName = "Eliza";

    function __construct() {
        $this->fromInternet();
        $this->fromCommandLine();
    }

    function fromInternet() {
        if ( isset($_POST['command']) ) {
            $this->command = $_POST['command'];
            $this->text = $_POST['text'];
            $this->token = $_POST['token'];
            $this->channelId = $_POST['channel_id'];
            $this->userName = $_POST['user_name'];
        }
    }

    function fromCommandLine() {
        global $argv;

        $this->text = $argv;              // ignore squiggly as this is a global php variable
        array_shift($this->text);         // remove first argument
        $this->text = join(" ", $this->text);
        $this->isSlack = 0;
    }


    
}