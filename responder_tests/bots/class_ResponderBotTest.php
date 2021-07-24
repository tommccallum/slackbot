<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_ResponderBotTest extends TestCase
{
    // public function test_complex_message_1(): void
    // {

    //     $testCaseContents = file_get_contents(__DIR__."/../messages/complex_message_1.json");
    //     $json = json_decode($testCaseContents, true);
    //     $app = new App($json);
    //     $app->botSelectionName = "ResponderBot";
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
    //     $app->botSelectionName = "ResponderBot";
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
    //     $app->botSelectionName = "ResponderBot";
    //     $bot = createNewBot($app);
    //     loadIntents($bot);
    //     $text = $bot->handle($app);
    //     var_dump($text);
    // }

    public function test_recommendation_message_1(): void
    {

        $testCaseContents = file_get_contents(__DIR__."/../messages/recommendation_message_1.json");
        $json = json_decode($testCaseContents, true);
        $app = new App($json);
        $app->botSelectionName = "ResponderBot";
        $bot = createNewBot($app);
        loadIntents($bot);
        $text = $bot->handle($app);
        var_dump($text);
    }
}
