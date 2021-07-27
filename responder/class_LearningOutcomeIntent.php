<?php

/**
 * Given a question like "What is learning outcome LO1.2.3.4?"
 * It will give the definition.
 * If its not valid it should say so.
 */
class LearningOutcomeIntent
{
    // map of TOPIC => list of resources
    private $csv;

    public function __construct()
    {
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
            if ($item['exampleNode']['text'] == "lo") {
                if (count($item['matchedNodes']) == 0) {
                    return "Sorry, I did not understand that.  You had a question about learning outcomes?";
                } else {
                    $lo = $item['matchedNodes'][0];
                }
            }
        }

        $learningOutcomeToFind = $lo['value'];
        var_dump($learningOutcomeToFind);
        $loText = null;
        foreach ($this->csv as $row) {
            var_dump($row);
            if ($row[6] == $learningOutcomeToFind) {
                $loText = $row[7];
                break;
            }
        }
        if (!isset($loText)) {
            return "Sorry, that does not appear to be a learning outcome.  You can find all the learning outcomes in the VLE under Student Guidance and Assessment.";
        }
        $response = "Found the learning outcome for you.";

        $loFormattedText = formatLearningOutcome($loText);

        $response .= "\n>".$loFormattedText;
        
        $response .= "\n\nDon't forget to ask for help or get one of the delivery staff to give you feedback on your portfolio.";

        return $response;
    }
}
