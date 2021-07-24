<?php

$file = __DIR__."/../responder/data/bookmarks.html";

$contents = file_get_contents($file);

$dom = new DOMDocument();
$dom->loadHTML($contents);

function walk_dom($node, &$results, &$topic="__general") {
    if ( !isset($node) ) {
        return;
    }
    if ( $node->nodeName == "h3" ) {
        $topic = $node->nodeValue;
        $results[ $topic ] = [];
    } else if ( $node->nodeName == "a" ) {
        $link = $node->attributes->getNamedItem("href")->value;
        $text = $node->textContent;
        $results[$topic ][] = array( 'name' => $text, 'url' => $link );
    } else {
        // var_dump("THISNODE");
        // var_dump($node);
        foreach ($node->childNodes as $child) {
        //     var_dump("CHILD");
            walk_dom($child, $results, $topic);
        }
        // var_dump("SIBLING");
        // if (isset($node->nextSibling)) {
        //     walk_dom($node->nextSibling, $results, $topic);
        // }
        // var_dump("ATTRIBUTES");
        // if ( isset($node->attributes)  && $node->attributes->count() > 0) {
        //     var_dump($node->attributes);
        //     foreach ($node->attributes as $child) {
        //         var_dump("ATTRIBUTE");
        //         walk_dom($child);
        //     }
        // }
    }
    
}

$bookmarks = [];
walk_dom($dom, $bookmarks);

$cache = __DIR__ . "/../responder/data/bookmarks/cache";
mkdir($cache, 0777, true);

foreach( $bookmarks as $topic => $links ) {
    $dirTopicName = preg_replace("/\s/", "_", $topic);
    $downloadDir = $cache . DIRECTORY_SEPARATOR . $dirTopicName;
    mkdir($downloadDir, 0777, true);

    foreach( $links as $index => $item ) {
        $info = pathinfo($item['url']);
        if ( isset($info['extension']) ) {
            $ext = strtolower($info['extension']);
            if ( $ext == "pdf" || $ext == "html" || $ext == "txt" || $ext == "csv" || $ext == "jpg" 
                || $ext == "png" || $ext == "docx" ) {
                // these are valid
            } else {
                print("[WARNING] extension found of $ext, replacing with .html\n");
                // if we are here then it means we had a extension like .com or .uk
                $ext = "html";
            }
        } else {
            $ext = "html";
        }
        $fileName = sha1($item['url']);
        $path = $downloadDir . DIRECTORY_SEPARATOR . $fileName . "." . $ext;
        printf("Downloading %s\n", $item['url']);
        if ( ($contents = file_get_contents($item['url'])) === false ) {
            printf("Failed to download '%s' from '%s'\n", $item['name'], $item['url'], $path);
            $bookmarks[$topic][$index]['path'] = false;
            $bookmarks[$topic][$index]['last_download_attempt'] = time();
        } else {
            if ( file_put_contents( $path, $contents ) ) {
                printf("Downloaded '%s' from '%s' to '%s'\n", $item['name'], $item['url'], $path);
                $bookmarks[$topic][$index]['path'] = $path;
                $bookmarks[$topic][$index]['last_download_attempt'] = time();
            } else {
                printf("Failed to save '%s' from '%s'\n", $item['name'], $item['url'], $path);
                $bookmarks[$topic][$index]['path'] = false;
                $bookmarks[$topic][$index]['last_download_attempt'] = time();
            }
        }
    }
}

$metafile = __DIR__ . "/../responder/data/bookmarks/meta.json";
file_put_contents($metafile, json_encode($bookmarks));