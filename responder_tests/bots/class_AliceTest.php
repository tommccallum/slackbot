<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class class_AliceTest extends TestCase
{
    // public function test_complex_message_1(): void
    // {

    //     $testCaseContents = file_get_contents(__DIR__."/../messages/complex_message_1.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "Alice";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    // public function test_about_message_1(): void
    // {

    //     $testCaseContents = file_get_contents(__DIR__."/../messages/about_message_1.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "Alice";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    // public function test_wellness_message_1(): void
    // {

    //     $testCaseContents = file_get_contents(__DIR__."/../messages/wellness_message_1.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "Alice";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    // public function test_recommendation_message_1(): void
    // {

    //     $testCaseContents = file_get_contents(__DIR__."/../messages/recommendation_message_1.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "Alice";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    // public function test_recommendation_message_1(): void
    // {

    //     $testCaseContents = file_get_contents(__DIR__."/../messages/buggy_messages_1.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "Alice";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    // public function test_recommendation_message_1(): void
    // {
    //     $testCaseContents = file_get_contents(__DIR__."/../messages/recommendation_message_2.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "Alice";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    // public function test_learning_outcome_intent_message_1(): void
    // {
    //     $testCaseContents = file_get_contents(__DIR__."/../messages/learning_outcome_intent_message_1.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "Alice";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    // public function test_learning_outcome_recommender_intent_message_1(): void
    // {
    //     $testCaseContents = file_get_contents(__DIR__."/../messages/learning_outcome_recommender_intent_message_1.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "Alice";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    // public function test_learning_outcome_recommender_intent_message_2(): void
    // {
    //     $testCaseContents = file_get_contents(__DIR__."/../messages/learning_outcome_recommender_intent_message_2.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "Alice";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    public function test_stop_warnings(): void
    {
        $this->assertSame(1, 1);
    }
}
