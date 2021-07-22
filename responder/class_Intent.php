<?php

class Intent
{
    private $name = "";
    private $examples = [];
    private $selectionMethod = "";
    private $replies = [];

    public function name() {
        return $this->name;
    }

    public function getReply() {
        if ( $this->selectionMethod == "random" ) {
            $n = rand(0, count($this->replies));
            return $this->replies[$n];
        } else {
            # by default returns first reply
            return $this->replies[0];
        }
    }

    public function isLike($str) {
        # compare $str to the examples in some way.
    }

    public function loadFromFile($path) {
        if ( file_exists($path) ) {
            $contents = file_get_contents($path);
            $json = json_decode($contents, true);
            if (isset($json['name'])) {
                $this->name = $json['name'];
            }
            if (isset($json['examples'])) {
                $this->examples = $json['examples'];
            }
            if (isset($json['selection'])) {
                $this->selectionMethod = $json['selection'];
            }
            if (isset($json['replies'])) {
                $this->replies = $json['replies'];
            }
        }
    }
}