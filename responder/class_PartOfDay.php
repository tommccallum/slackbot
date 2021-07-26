<?php

class PartOfDay 
{
    private $data = [];

    public function __construct($file=__DIR__."/data/part_of_day.json") {
        if ( file_exists($file) === false ) {
            throw new \Exception("could not locate ".$file);
        }
        $contents = file_get_contents($file);
        $this->data = json_decode($contents, true);
    }

    # these items all return arrays of words that can be selected from

    public function getText() {
        $hour = intval(date("%H"));
        foreach( $this->data as $item ) {
            if ( intval($item['min']) >= $hour && $hour < intval($item['max']) ) {
                return $item['text'];
            }
        }
        return null;
    }

    public function getMeal() {
        $hour = intval(date("%H"));
        foreach( $this->data as $item ) {
            if ( intval($item['min']) >= $hour && $hour < intval($item['max']) ) {
                return $item['meal'];
            }
        }
        return null;
    }

    public function getNextMeal() {
        $hour = intval(date("%H"));
        foreach( $this->data as $item ) {
            if ( intval($item['min']) >= $hour && $hour < intval($item['max']) ) {
                return $item['nextMeal'];
            }
        }
        return null;
    }

    public function get($text) {
        var_dump($this->data);
        if ( $text === "meal" ) {
            return $this->getMeal()[0];
        } else if ( $text === "nextMeal" ) {
            return $this->getNextMeal()[0];
        } else if ( $text == "text") {
            return $this->getText()[0];
        } else {
            return "";
        }
    }
}