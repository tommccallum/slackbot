<?php

# This trains a new document model so that we can match a phrase to the best document in our bookmark list
# we look at both the title and the contents.

require_once("../responder/class_DocumentClassifier.php");

$bookmarksPath = __DIR__."/../responder/data/bookmarks";
$metafile = $bookmarksPath . "/meta.json";
$documentPath = $bookmarksPath . "/cache";
$saveModelPath = __DIR__."/../models/bookmarks.json";

$classifier = new DocumentClassifier();

$contents = file_get_contents($metafile);
$meta = json_decode($contents, true);

foreach( $meta as $topic => $links ) {
    foreach( $links as $item ) {
        if ( $item['path'] ) {
            $path = $item['path'];
            $url = $item['url'];
            $info = pathinfo($path);
            if ( !isset($path) ) {
                throw new \Exception("failed to find path ".$item['path']);
            }
            $type = null;
            $temporaryFile = null;
            if ( isset($info['extension']) ) {
                if ( $info['extension'] == "html") {
                    $type = "HTML";
                } else {
                    $type = "pdf";
                }
            } 
            if ( $type == "pdf" ) {
                $pathToContent = tempnam("/tmp", "slackbot_");
                $temporaryFile = true;
                print("EXEC: pdftotext $path $pathToContent\n");
                system("pdftotext $path $pathToContent");
                $type = "TEXT";
            } else {
                $pathToContent = $path;
            }
            $contents = file_get_contents($pathToContent);
            if ( isset($temporaryFile) ) {
                unlink($pathToContent);
                $temporaryFile = null;
            }
            if ( $type == "HTML" ) {
                $dom = new DOMDocument();
                $dom->loadHTML($contents);
                $text = $dom->textContent;
                $words = explode(" ",$text);
                $wordCount = min(count($words), 1000);
                $chosenWords = [];
                for($ii=0; $ii < $wordCount; $ii++ ) {
                    $chosenWords[] = $words[$ii];
                }
                $selectedText = join(" ", $chosenWords);
                $text = $item['name']." ".$selectedText;
                $classifier->addExample($url, $text);
            } else if ( $type == "TEXT" ) {
                $words = explode(" ",$contents);
                $wordCount = min(count($words), 1000);
                $chosenWords = [];
                for($ii=0; $ii < $wordCount; $ii++ ) {
                    $chosenWords[] = $words[$ii];
                }
                $selectedText = join(" ", $chosenWords);
                $text = $item['name']." ".$selectedText;
                $classifier->addExample($url, $text);
            } else {
                throw new \Exception("Unsupported ".$path);
            }

        }
    }
}

$classifier->saveModel($saveModelPath);


