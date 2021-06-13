<?php

abstract class Bot
{
    public $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function printInfo() {
        /** Do nothing its a virtual function to be overriden by each bot */
    }

    public function respond($question) {
        /** Do nothing and override */
        return null;
    }

    public function ask($userInputText)
    {
        $response = $this->respond($userInputText);
        if ($response == null || strlen($response) == 0) {
            $response = "Sorry, I am new around here, I do not understand your question.";
        }
        return $response;
    }
}

