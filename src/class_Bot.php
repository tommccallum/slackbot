<?php

abstract class Bot
{
    public function __construct()
    {
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



    // Main dispatch method to handle the different events that we will receive.
    public function handle($app) {
        if ( $app->isCommand() ) {
            return $this->obey($app);
        }
        if ($app->isEvent()) {
            if ($app->type == "app_mention") {
                return $this->onAppMention($app);
            } else if ($app->type == "message") {
                return $this->onMessage($app);
            } else {
                return "Sorry, I do not understand how to respond to a '"+$app->event['type']+"' message.";
            }
        }
    }
}

