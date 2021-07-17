<?php

# Just sends back a test response when used
class ElizaBot extends Bot
{
    public $mode = "Test Bot";

    public function respond($question)
    {
        $response = "Hi, this is a test response.";
        return $response;
    }
}