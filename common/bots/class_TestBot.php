<?php

# Just sends back a test response when used
class TestBot extends Bot
{
    public $mode = "Test Bot";

    protected function respond($question)
    {
        $response = "Hi, this is a test response at ".date("H:m")." on ".date("l jS F Y").".";
        return $response;
    }


    protected function onMessage()
    {
        savelog("TestBot::onMessage");
        sendSlackReaction($this->app, "thumbsup");
        $response = "Hi, this is a test response at ".date("H:m")." on ".date("l jS F Y").".";
        return $response;
    }

    protected function onSomeoneHasJoinedTheChannel()
    {
        return "Welcome <@".$this->app->event['user'].">";
    }
}
