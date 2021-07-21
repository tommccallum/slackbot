<?php

class Me
{
    private $attributes = [];

    public function __construct() {

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
}