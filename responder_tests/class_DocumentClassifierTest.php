<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_DocumentClassifierTest extends TestCase
{
    public function test_documentclassifier_test(): void
    {
        $model = new DocumentClassifier();
        $model->loadModel(__DIR__."/../models/bookmarks.json");

        $result = $model->classify("hacking");
        #var_dump($result);
        $this->assertSame($result, "https://www.kali.org/");

      
    }
}

