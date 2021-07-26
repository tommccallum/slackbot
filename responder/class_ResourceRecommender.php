<?php

class ResourceRecommender
{
    // map of TOPIC => list of resources
    private $resources = [];
    private $documentRecommender;

    public function __construct() {
        // HACK we hack this in here as in the Intents we load directory
        // and we don't know anything about the personality file there.
        $this->documentRecommender = new DocumentClassifier();
        $this->documentRecommender->loadModel(__DIR__."/../models/bookmarks.json");
        $contents = file_get_contents(__DIR__."/data/bookmarks/meta.json");
        $this->resources = json_decode($contents, true);
    }

    public function loadFromFile($path) {
        if ( file_exists($path) ) {
            $contents = file_get_contents($path);
            $this->attributes = json_decode($contents, true);
        } else {
            throw new \Exception("Unable to load resource file from ".$path.".");
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
        $str = "You can also check out <https://rl.talis.com/3/uhi/lists/40F502DF-66AD-61B2-F3B2-93D993BF638F.html?lang=en-GB&login=1|the reading list>.";
        $request = $matchedIntent['variables']['topic']['value'];
        $url = $this->documentRecommender->classify($request);
        $response = "";
        if ( isset($url) ) {
            $chosenItem = null;
            foreach ($this->resources as $topic => $links) {
                foreach ($links as $item) {
                    if ($item['url'] === $url) {
                        $item['topic'] = $topic;
                        $item['reference_count'] = count($links);
                        $chosenItem = $item;
                        break;
                    }
                }
                if (isset($chosenItem)) {
                    break;
                }
            }

            if (isset($chosenItem)) {
                $response .= "You could try this resource that I saw in the reading list, its called <"
                            . $chosenItem['url']."|" . $chosenItem['name'] . "> and I found it in the "
                            . $chosenItem['topic']." section with ".$item['reference_count']." other resources you could look at.";
                $response .= "\n\n$str";
            } else {
                $response .= "Sorry, I could not find anything on the reading list about that.";
            }
        } else {
            $response .= "Sorry, I could not find anything on the reading list about that.";
        }

        $response .= "\n\nI suppose you could always try IBM Academic Initiative resources, Google or LinkedIn Learning :smiley:.";
        return $response;
    }

}