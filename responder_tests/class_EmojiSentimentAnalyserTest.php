<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_EmojiSentimentAnalyserTest extends TestCase
{
    public function test_classify(): void
    {
        $s = new EmojiSentimentAnalyser();
        $s->loadModel(__DIR__."/../responder/data/emoticons_sentiment.txt");
        $value = $s->classify("smile");
        $this->assertSame($value, 4.0);
    }

    public function test_classify_array(): void
    {
        $s = new EmojiSentimentAnalyser();
        $s->loadModel(__DIR__."/../responder/data/emoticons_sentiment.txt");
        $value = $s->classify(["smile","worried"]);
        $this->assertSame($value, (4.0+0.0)/2.0);
    }
}