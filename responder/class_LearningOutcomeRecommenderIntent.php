<?php

class LearningOutcomeRecommenderIntent
{
    // map of TOPIC => list of resources
    private $documentRecommender;
    private $csv = [];

    public function __construct()
    {
        // HACK we hack this in here as in the Intents we load directory
        // and we don't know anything about the personality file there.
        $this->documentRecommender = new DocumentClassifier();
        $this->documentRecommender->loadModel(__DIR__."/../models/learning_outcome_model.json");
        $this->csv = array_map("str_getcsv", file(__DIR__."/data/learning_outcome_details.csv"));
    }

    public function loadFromFile($path)
    {
        if (file_exists($path)) {
            $contents = file_get_contents($path);
            $this->attributes = json_decode($contents, true);
        } else {
            throw new \Exception("Unable to load resource file from ".$path.".");
        }
    }

    public function set($key, $value)
    {
        $key = strtolower($key);
        $this->attributes[$key] = $value;
    }

    public function get($key)
    {
        $key = strtolower($key);
        return $this->attributes[$key];
    }

    public function getKeys()
    {
        return array_keys($this->attributes);
    }

    public function match($matchedIntent)
    {
        // var_dump($matchedIntent);
        // at the moment we only return one match but this could be more maybe?
        $topicTextArray = [];
        foreach ($matchedIntent['match'][0]['matches'] as $item) {
            if ($item['exampleNode']['text'] == "topic") {
                if (count($item['matchedNodes']) == 0) {
                    return "Sorry, I did not understand that.  It was something about a recommendation for a learning outcome?";
                } else {
                    foreach ($item['matchedNodes'] as $node) {
                        $topicTextArray[] = $node['text'];
                    }
                }
            }
        }
        $request = join(" ", $topicTextArray);
        savelog("Learning Outcome Recommendation for: ".$request);
        $loID = $this->documentRecommender->classify($request, true);
        $response = "";
        if (isset($loID)) {
            // try and enforce the year
            if (preg_match("/year\s+(\d)/", $request, $match)) {
                $year = $match[1];
                if (key($loID)[2] == $year) {
                    $loID = key($loID);
                } else {
                    $found = false;
                    foreach ($loID as $k => $v) {
                        if ($k[2] == $year) {
                            $loID = $k;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $loID = null;
                    }
                }
            } else {
                # just take top
                $loID = key($loID);
            }

            $chosenItem = null;
            if (isset($loID)) {
                foreach ($this->csv as $row) {
                    if ($row[6] == $loID) {
                        $chosenItem = $row;
                        break;
                    }
                }
            }

            if (isset($chosenItem)) {
                $response = "The best learning outcome I found that matched your topic was $loID.  This is in the category of '".$chosenItem[3]
                            ."' for module '".$chosenItem[2]."'. ";
                $response  .= "If this is for the wrong year then just let me know and I will look again.\n\n";

                $loFormattedText = formatLearningOutcome($chosenItem[7]);
                $response .= $loFormattedText;
            } else {
                $response .= "Sorry, I could not find anything on learning outcome $loID.";
            }
        } else {
            $response .= "Sorry, I could not find anything on that topic.";
        }

        return $response;
    }
}
