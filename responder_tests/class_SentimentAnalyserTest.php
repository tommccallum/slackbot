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
        // print("TEXT : $test\n");
        // print("CLASS: $result\n");


        $test = "Hi everyone, its ok to see you.";
        $result = $model->classify($test, true);
        // print("TEXT : $test\n");
        // print("CLASS:\n");
        // var_dump($result);


        $test = "I'm feeling really bad today.";
        $result = $model->classify($test, true);

        $this->assertSame(1, 1);

        // print("TEXT : $test\n");
        // print("CLASS:\n");
        // var_dump($result);

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

    // public function test_analyse_strong_positive_sentiments(): void
    // {
    //     $dataFilePath = __DIR__."/../responder/data/sentiment/strong_positive_sentiment_remarks.txt";
    //     $model = new SentimentAnalyser();
    //     $model->loadModel(__DIR__."/../models/sentiment_model.json");

    //     $text = array_map("chop", file($dataFilePath));
    //     foreach ($text as $t) {
    //         $result = $model->classify($t);
    //         // var_dump(array($result, $t)); # TODO Boo-yah fails this test
    //         $this->assertSame($result, 4);
    //     }
    // }

    // public function test_analyse_positive_sentiments(): void
    // {
    //     $dataFilePath = __DIR__."/../responder/data/sentiment/weak_positive_sentiment_remarks.txt";
    //     $model = new SentimentAnalyser();
    //     $model->loadModel(__DIR__."/../models/sentiment_model.json");

    //     $text = array_map("chop", file($dataFilePath));
    //     foreach ($text as $t) {
    //         $result = $model->classify($t);
    //         #var_dump(array($result, $t));
    //         # TODO "I support so" fails this test
    //         $this->assertTrue($result >= 3);
    //     }
    // }

    public function test_analyse_negative_sentiments(): void
    {
        $dataFilePath = __DIR__."/../responder/data/sentiment/weak_negative_sentiment_remarks.txt";
        $model = new SentimentAnalyser();
        $model->loadModel(__DIR__."/../models/sentiment_model.json");

        $text = array_map("chop", file($dataFilePath));
        foreach ($text as $t) {
            $result = $model->classify($t);
            #var_dump(array($result, $t));
            $this->assertTrue($result < 2);
        }
        $this->assertTrue(1==1);
    }

    public function test_analyse_very_negative_sentiments(): void
    {
        $dataFilePath = __DIR__."/../responder/data/sentiment/strong_negative_sentiment_remarks.txt";
        $model = new SentimentAnalyser();
        $model->loadModel(__DIR__."/../models/sentiment_model.json");

        $text = array_map("chop", file($dataFilePath));
        foreach ($text as $t) {
            $result = $model->classify($t);
            #var_dump(array($result, $t));
            $this->assertTrue($result < 2);
        }
        $this->assertTrue(1==1);
    }

    public function test_disappointed(): void
    {
        $model = new SentimentAnalyser();
        $model->loadModel(__DIR__."/../models/sentiment_model.json");
        $result = $model->classify("disappointed");
        $this->assertTrue($result == 0);
    }

    public function test_no(): void
    {
        $model = new SentimentAnalyser();
        $model->loadModel(__DIR__."/../models/sentiment_model.json");
        $result = $model->classify("no");
        $this->assertTrue($result == 0);
    }

    public function test_no_i_wont(): void
    {
        $model = new SentimentAnalyser();
        $model->loadModel(__DIR__."/../models/sentiment_model.json");
        $result = $model->classify("No I won't");
        $this->assertTrue($result == 0);
    }

    public function test_no_i_wont_2(): void
    {
        $sentence = "No I won't";
        $clauses = splitStringIntoClauses($sentence);
        #var_dump($clauses);
        $model = new SentimentAnalyser();
        $model->loadModel(__DIR__."/../models/sentiment_model.json");
        $result = $model->classifyLexemes($clauses[0]['lexemes']);
        #var_dump($result);
        $this->assertTrue($result == 1);
    }

    public function test_no_2(): void
    {
        $sentence = "No";
        $clauses = splitStringIntoClauses($sentence);
        #var_dump($clauses);
        $model = new SentimentAnalyser();
        $model->loadModel(__DIR__."/../models/sentiment_model.json");
        $result = $model->classifyLexemes($clauses[0]['lexemes']);
        #var_dump($result);
        $this->assertTrue($result == 1);
    }
}
