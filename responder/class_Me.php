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
        # here we are just going to look for the key in the question
        # its a a bit simple and we can improve it later on to look
        # for synonyms.
        $str = $matchedIntent['matched_example'];
        foreach( $this->attributes as $key => $value ) {
            if ( strpos(strtolower($str), strtolower($key)) !== false ) {
                return $value;
            }
        }
        return "Sorry, thats too personal!";
    }

}