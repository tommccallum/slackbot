<?php

//
// We want to rate the sentiment of each message so we can either sympathise or try and help.
// This is a simple classifier I found on the web.
// https://accidentalfactors.com/bayesian-opinion-mining/ (I have changed the variables etc to suite my requirements.)
//
// Most simple sentiment analysis uses a bag of words with a Naive bayes classifier so not too difficult and
// that is what we will use here as we want something quick, rough and ready.
//
// Uses https://gist.github.com/sebleier/554280 list of stop words.

class SentimentAnalyser
{
    private $index = array();
    // our output classes
    // we want classes from 0-4
    private $classes = array('very negative', 'negative', 'neutral', 'positive', 'very positive');

    // count how many words are positive and how many negative
    private $classTokCounts = array('very negative' => 0, 'negative' => 0, 'neutral' => 0, 'positive' => 0, 'very positive' => 0);

    // total word count
    private $tokCount = 0;

    // how many messages of each that we see
    private $classDocCounts = array('very negative' => 0, 'negative' => 0, 'neutral' => 0, 'positive' => 0, 'very positive' => 0);

    // how many messages in total that we see
    private $docCount = 0;

    // what is our preexistent probabiliy of a positive/negative message.
    private $prior = array('very negative' => 0.2, 'negative' => 0.2, 'neutral' => 0.2, 'positive' => 0.2, 'very positive' => 0.2);

    private $stopWordList = array();
    private $stopWordFile = __DIR__."/data/stopwords.txt";

    private $remarks = array();

    public function __construct()
    {
        $this->remarks["very negative"] = file(__DIR__."/data/sentiment/strong_negative_sentiment_remarks.txt");
        $this->remarks["negative"] = file(__DIR__."/data/sentiment/weak_negative_sentiment_remarks.txt");
        $this->remarks["positive"] = file(__DIR__."/data/sentiment/weak_positive_sentiment_remarks.txt");
        $this->remarks["very positive"] = file(__DIR__."/data/sentiment/strong_positive_sentiment_remarks.txt");

        foreach ($this->remarks as $k => $arr) {
            $this->remarks[$k] = array_map("strtolower", array_map("chop", $arr));
        }
    }

    public function addExample($class, $line)
    {
        if (is_integer($class)) {
            $classIndex = $class;
            $className = $this->classes[$classIndex];
        } else {
            $className = $class;
            $classIndex = array_search($class, $this->classes);
        }

        $this->docCount++;
        $this->classDocCounts[$className]++;
        $tokens = $this->tokenise($line);
        foreach ($tokens as $token) {
            if (!isset($this->index[$token][$className])) {
                $this->index[$token][$className] = 0;
            }
            $this->index[$token][$className]++;
            $this->classTokCounts[$className]++;
            $this->tokCount++;
        }
    }

    // reads in a file of examples of both negative and positive messages
    // we might use a movie database.
    // we can say only take the top N messages by using the limit, 0 takes them all.
    public function addToIndex($file, $class, $limit = 0)
    {
        $fh = fopen($file, 'r');
        $i = 0;
        if (!in_array($class, $this->classes)) {
            echo "Invalid class specified\n";
            return;
        }
        while ($line = fgets($fh)) {
            if ($limit > 0 && $i > $limit) {
                break;
            }
            $i++;
                    
            $this->docCount++;
            $this->classDocCounts[$class]++;
            $tokens = $this->tokenise($line);
            foreach ($tokens as $token) {
                if (!isset($this->index[$token][$class])) {
                    $this->index[$token][$class] = 0;
                }
                $this->index[$token][$class]++;
                $this->classTokCounts[$class]++;
                $this->tokCount++;
            }
        }
        fclose($fh);
    }
    
    public function calcPriors()
    {
        // change our priors to match the proportions in our initial data.
        foreach ($this->classes as $class) {
            $this->prior[$class] = $this->classDocCounts[$class] / $this->docCount;
        }
    }

    public function classifyLexemes($lexemes, $details=false)
    {
        $this->calcPriors();

        // tokenise the latest message
        $classScores = array();

        // for each class we will calculate the influence of each word
        // FIXME if no words were found then it ends up 4 which is just wrong.
        $allZero = true;
        foreach ($this->classes as $class) {
            $classScores[$class] = 1;
            foreach ($lexemes as $lexeme) {
                $token = $lexeme['text'];       # TODO maybe this should be value, not text
                #var_dump($token);
                $count = isset($this->index[$token][$class]) ?
                                    $this->index[$token][$class] : 0;
                if ($count > 0) {
                    $allZero = false;
                }
                $classScores[$class] *= ($count + 1) /
                                    ($this->classTokCounts[$class] + $this->tokCount);
            }
            $classScores[$class] = $this->prior[$class] * $classScores[$class];
        }
        
        // sort in descending order maintain index association
        arsort($classScores);
        
        if ($allZero) {
            $topClass = 2;
        } else {
            $topClass = array_search(key($classScores), $this->classes);
        }
        $bag = [];
        foreach ($lexemes as $lex) {
            $bag[] = $lex['text'];
        }
        $remarkClass = $this->classifyRemarks($bag);
        
        if ($remarkClass == 2) {
            $finalClass = $topClass;
        } elseif ($remarkClass < 2 && $topClass <= 2) {
            $finalClass = min($remarkClass, $topClass);
        } elseif ($remarkClass > 2 && $topClass >= 2) {
            $finalClass = max($remarkClass, $topClass);
        } else {
            $finalClass = $remarkClass;
        }

        if ($details) {
            return ($classScores);
        }

        // get the index of the highest value e.g. pos, neg
        return $finalClass;
    }

    public function classifyRemarks($bag)
    {
        $class = "neutral";
        foreach ($this->remarks as $classItem => $arr) {
            foreach ($arr as $phraseToMatch) {
                $remarkWords = explode(" ", $phraseToMatch);
                $startWord = $remarkWords[0];
                foreach ($bag as $index => $value) {
                    if ($value == $startWord) {
                        $match = true;
                        for ($ii=1; $ii < count($remarkWords); $ii++) {
                            if ($index + $ii < count($bag)) {
                                if ($bag[$index +$ii] != $remarkWords[$ii]) {
                                    $match = false;
                                    break;
                                }
                            }
                        }
                        if ($match) {
                            return array_search($classItem, $this->classes);
                        }
                    }
                }
            }
        }
        return array_search($class, $this->classes);
    }

    public function classify($document, $details=false)
    {
        $this->calcPriors();

        // tokenise the latest message
        $tokens = $this->tokenise($document);
        $classScores = array();

        // for each class we will calculate the influence of each word
        foreach ($this->classes as $class) {
            $classScores[$class] = 1;
            foreach ($tokens as $token) {
                $count = isset($this->index[$token][$class]) ?
                                    $this->index[$token][$class] : 0;

                $classScores[$class] *= ($count + 1) /
                                    ($this->classTokCounts[$class] + $this->tokCount);
            }
            $classScores[$class] = $this->prior[$class] * $classScores[$class];
        }
        
        // sort in descending order maintain index association
        $topClass = array_search(key($classScores), $this->classes);
        $remarkClass = $this->classifyRemarks($tokens);
        if ($remarkClass == 2) {
            $finalClass = $topClass;
        } elseif ($remarkClass < 2 && $topClass <= 2) {
            $finalClass = min($remarkClass, $topClass);
        } elseif ($remarkClass > 2 && $topClass >= 2) {
            $finalClass = max($remarkClass, $topClass);
        } else {
            $finalClass = $remarkClass;
        }


        if ($details) {
            return ($classScores);
        }

        // get the index of the highest value e.g. pos, neg
        return $finalClass;
    }

    // here we break up the message into its constituent words
    // - makes the lower case
    // - keeps only the words
    // TODO remove the words like 'a', 'the' etc
    private function tokenise($document)
    {
        if (count($this->stopWordList) == 0 && file_exists($this->stopWordFile)) {
            print("Reading stop word list from file");
            $this->stopWordList = file($this->stopWordFile);
            $this->stopWordList = array_map("strtolower", $this->stopWordList);
            $this->stopWordList = array_map("chop", $this->stopWordList);
            print("Read in ".count($this->stopWordList)." stop words");
        }
        #$document = strtolower($document);
        #preg_match_all('/\w+/', $document, $matches);
        
        $bagOfWords = explode(' ', $document);
        array_walk($bagOfWords, array($this, "cleanWord"));
        $usefulWords = array();
        foreach ($bagOfWords as $w) {
            if (in_array($w, $this->stopWordList)) {
                # ignore
            } else {
                $usefulWords[count($usefulWords)] = $w;
            }
        }
        return $usefulWords;
    }

    private function cleanWord(&$w)
    {
        $w = strtolower($w);
        $w = preg_replace('/\W/', '', $w);
    }

    public function report()
    {
        var_dump($this->classDocCounts);
    }

    public function saveModel($path)
    {
        $saveObject = array(
                                'stopWordList' => $this->stopWordList,
                                'stopWordFile' => $this->stopWordFile,
                                'index' => $this->index,
                                'classes' => $this->classes,
                                'classTokCounts' => $this->classTokCounts,
                                'tokCount' => $this->tokCount,
                                'classDocCounts' => $this->classDocCounts,
                                'docCount' => $this->docCount,
                                'prior' => $this->prior,
                            );
        $json = json_encode($saveObject);
        file_put_contents($path, $json);
    }

    public function loadModel($path)
    {
        $contents = file_get_contents($path);
        $json = json_decode($contents, true);
        $this->stopWordList = $json['stopWordList'];
        $this->stopWordFile = $json['stopWordFile'];
        $this->index = $json['index'];
        $this->classes = $json['classes'];
        $this->classTokCounts = $json['classTokCounts'];
        $this->tokCount = $json['tokCount'];
        $this->classDocCounts = $json['classDocCounts'];
        $this->docCount = $json['docCount'];
        $this->prior = $json['prior'];
    }
}
