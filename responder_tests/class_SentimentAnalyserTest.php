<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_SentimentAnalyserTest extends TestCase
{
    public function test_analyser(): void
    {
        $model = new SentimentAnalyser();
        $model->loadModel(__DIR__."/../models/sentiment_model.json");

        $test = "Hi everyone! Great to see you!";
        $result = $model->classify($test);
        print("TEXT : $test\n");
        print("CLASS: $result\n");


        $test = "Hi everyone, its ok to see you.";
        $result = $model->classify($test, true);
        print("TEXT : $test\n");
        print("CLASS:\n");
        var_dump($result);


        $test = "I'm feeling really bad today.";
        $result = $model->classify($test, true);
        print("TEXT : $test\n");
        print("CLASS:\n");
        var_dump($result);

        // $test = "It's an awful day.";
        // $result = $model->classify($test, true);
        // print("TEXT : $test\n");
        // print("CLASS:\n");
        // var_dump($result);

        // $test = "It's a really awful day.";
        // $result = $model->classify($test, true);
        // print("TEXT : $test\n");
        // print("CLASS:\n");
        // var_dump($result);

        // $test = "It's feeling really awful day.";
        // $result = $model->classify($test, true);
        // print("TEXT : $test\n");
        // print("CLASS:\n");
        // var_dump($result);

        // $test = "I'm feeling really awful day.";
        // $result = $model->classify($test, true);
        // print("TEXT : $test\n");
        // print("CLASS:\n");
        // var_dump($result);

        // $document = "I'm feeling awful today 100% bad.";
        // var_dump($document);
        // preg_match_all('/\w+/', $document, $matches);
        // function cleanWord(&$w) {
        //     $w = strtolower($w);
        //     $w = preg_replace('/\W/', '', $w);
        // }
    }
}

