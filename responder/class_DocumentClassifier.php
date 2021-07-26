<?php

// This simple document classifier works the same as the Sentiment Analyser to 
// get a best fit of all the documents.
// Each document is a CLASS

class DocumentClassifier
{
    private $index = array();

    // each class is a document
    private $classes = array();

    // count how many of each word appear in each document
    private $classTokCounts = array();

    // total word count
    private $tokCount = 0;

    // how many instances of this document we see - this will always be 1
    private $classDocCounts = array();

    // how many documents in total that we see
    private $docCount = 0;

    // because all our instances will be 1 our prior will be 1 / num documents
    private $prior = array();

    // remove common words to make our search a bit more useful.
    private $stopWordList = array();
    private $stopWordFile = __DIR__."/data/stopwords.txt";

    public function addExample( $url, $document )
    {
        $this->classes[] = $url;
        if ( !isset($this->classDocCounts[$url]) ) {
            $this->classDocCounts[$url] = 0;
        } 
        if ( !isset($this->classTokCounts[$url]) ) {
            $this->classTokCounts[$url] = 0;
        }

        $this->docCount++;
        $this->classDocCounts[$url]++;
        $tokens = $this->tokenise($document);
        foreach ($tokens as $token) {
            if (!isset($this->index[$token][$url])) {
                $this->index[$token][$url] = 0;
            }
            $this->index[$token][$url]++;
            $this->classTokCounts[$url]++;
            $this->tokCount++;
        }
    }

    public function calcPriors()
    {
        // change our priors to match the proportions in our initial data.
        foreach( $this->classes as $class) {
            $this->prior[$class] = $this->classDocCounts[$class] / $this->docCount;
        }
    }

    public function classify($userText, $details=false)
    {
        $this->calcPriors();

        // tokenise the latest message
        $tokens = $this->tokenise($userText);
        $classScores = array();

        // for each class we will calculate the influence of each word
        $numberOfHits = 0;
        foreach ($this->classes as $class) {
            $classScores[$class] = 1;
            foreach ($tokens as $token) {
                $count = isset($this->index[$token][$class]) ?
                                    $this->index[$token][$class] : 0;
                $numberOfHits += $count;

                // if the word does not have a value for that class then we don't use it.
                #$classScores[$class] *= ($count + 1) /
                #                ($this->classTokCounts[$class] + $this->tokCount);
            }
            #$classScores[$class] = $this->prior[$class] * $classScores[$class];
            $classScores[$class] = $count;
        }
        
        if ( $numberOfHits == 0 ) {
            return null;
        }

        // sort in descending order maintain index association
        arsort($classScores);

        if ( $details ) {
            return ( $classScores);
        }

        // debug the top 5
        savelog("Document Classifier: $userText");
        $keys = array_keys($classScores);
        for ($ii=0; $ii < min(count($classScores), 5); $ii++) {
            savelog($classScores[$keys[$ii]].": ".$keys[$ii]);
        }

        // get the index of the highest value e.g. pos, neg
        return key($classScores);
    }

    // here we break up the message into its constituent words
    // - makes the lower case
    // - keeps only the words
    // TODO remove the words like 'a', 'the' etc
    private function tokenise($document)
    {
        if ( count($this->stopWordList) == 0 && file_exists($this->stopWordFile) ) {
            print("Reading stop word list from file\n");
            $this->stopWordList = file($this->stopWordFile);
            $this->stopWordList = array_map("strtolower", $this->stopWordList);
            $this->stopWordList = array_map("chop",$this->stopWordList);
            print("Read in ".count($this->stopWordList)." stop words\n");
        }
        #$document = strtolower($document);
        #preg_match_all('/\w+/', $document, $matches);
        
        $bagOfWords = explode(' ', $document);
        array_walk($bagOfWords, array($this, "cleanWord"));
        $usefulWords = array();
        foreach( $bagOfWords as $w ) {
            if ( in_array($w, $this->stopWordList) ) {
                # ignore
            } else {
                $usefulWords[count($usefulWords)] = $w;
            }
        }
        return $usefulWords;
    }

    private function cleanWord(&$w) {
        $w = strtolower($w);
        $w = preg_replace('/\W/', '', $w);
    }

    public function report() {
        var_dump($this->classDocCounts);
    }

    public function saveModel($path) {
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

    public function loadModel($path) {
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



