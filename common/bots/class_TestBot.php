<?php

# Just sends back a test response when used
class TestBot extends Bot
{
    public $mode = "Test Bot";

    protected function respond($question) {
        $response = "Hi, this is a test response at ".date("H:m")." on ".date("l jS F Y").".";
        return $response;
    }


    protected function onMessage($app)
    {
        savelog("TestBot::onMessage");
        sendSlackReaction($app, "thumbsup");
        $response = "Hi, this is a test response at ".date("H:m")." on ".date("l jS F Y").".";
        return $response;
    }

    protected function onSomeoneHasJoinedTheChannel($app) {
        return "Welcome <@".$app->event['user'].">";
    }

}