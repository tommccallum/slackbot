<?php

class Me
{
    private $attributes = [];

    public function __construct() {
        // HACK we hack this in here as in the Intents we load directory
        // and we don't know anything about the personality file there.
        $this->loadFromFile(__DIR__."/data/personality.json");
    }

    public function loadFromFile($path) {
        if ( file_exists($path) ) {
            $contents = file_get_contents($path);
            $this->attributes = json_decode($contents, true);
        } else {
            throw new \Exception("Unable to load personality attributes from ".$path.".");
        }
    }

    public function set($key, $value) {
        $key = strtolower($key);
        $this->attributes[$key] = $value;
    }

    public function get($key) {
        $key = strtolower($key);
        return $this->attributes[$key];
    }

    public function getKeys() {
        return array_keys($this->attributes);
    }

    public function match($matchedIntent) {
        # here because we changed the matching method
        # we don't have anything to select so we have hand matched.
        # ideally we would match the examples to the answer.
        $index = $matchedIntent['match'][0]['example_index'];
        switch($index) {
            case 0:
                return $this->get("live");
            case 1:
                return $this->get("name");
            case 2:
                return $this->get("age");
            case 3:
                return $this->get("age");
            default:
                return "Sorry, thats too personal!";
        }
    }

}