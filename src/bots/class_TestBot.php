<?php

# Just sends back a test response when used
class TestBot extends Bot
{
    public $mode = "Test Bot";

    public function respond($question)
    {
        $response = "Hi, this is a test response at ".date("H:m")." on ".date("l jS F Y").".";
        return $response;
    }
}