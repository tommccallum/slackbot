<?php

abstract class Bot
{
    private $intents = [];
    private $partOfDay = [];

    public function __construct()
    {
    }

    public function getPartOfDay() {
        return $this->partOfDay;
    }

    public function setPartOfDay($partOfDay) {
        $this->partOfDay = $partOfDay;
    }

    public function addIntent($intent) {
        array_push($this->intents, $intent);
    }

    public function removeIntent($name) {
        foreach( $this->intents as $key => $value ) {
            if ( $value->name() == $name ) {
                unset($this->intents[$key]);
                return;
            }
        }
    }

    public function printInfo() {
        /** Do nothing its a virtual function to be overriden by each bot */
    }

    protected function respond($question) {
        /** Do nothing and override */
        return null;
    }

    protected function obey($app)
    {
        $response = $this->respond($app->text);
        if ($response == null || strlen($response) == 0) {
            $response = "Sorry, I am new around here, I do not understand your question.";
        }
        return $response;
    }

    protected function onAppMention($app) {
        return "I understand an AppMention event, but there is nothing I can do.";
    }

    protected function onMessage($app) {
        return "I understand an Message event, but there is nothing I can do.";
    }

    protected function onSomeoneHasJoinedTheChannel($app) {
        return "Welcome friend!";
    }

    protected function newBotAddedToChannel($app) {
        return null;
    }

    // Main dispatch method to handle the different events that we will receive.
    public function handle($app) {
        if ( $app->isCommand() ) {
            savelog("Bot::handle(command)");
            return $this->obey($app);
        }
        if ($app->isEvent()) {
            $eventType = $app->event['type'];
            $eventSubtype = null;
            if (isset($app->event['subtype'])) {
                $eventSubtype = $app->event['subtype'];
            }
            savelog("Bot::handle(event=".$eventType .")");
            if ($eventType == "app_mention") {
                return $this->onAppMention($app);
            } else if ( $eventType == "member_left_channel") {
                
            } else if ( $eventType == "member_join_channel") {

            } else if ($eventType == "message") {
                if (isset($eventSubtype)) {
                    if ( $eventSubtype == "channel_join" ) {
                        return $this->onSomeoneHasJoinedTheChannel($app);
                    } else if ( $eventSubtype == "bot_add") {
                        return $this->newBotAddedToChannel($app);
                    } else {
                        return $this->onMessage($app);
                    }
                } else {
                    return $this->onMessage($app);
                }
            } else {
                return "Sorry, I do not understand how to respond to a '"+$eventType+"' message.";
            }
        }
        savelog("Bot::handle(null)");
        return null;
    }
}

