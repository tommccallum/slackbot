<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

autoload_environment();

final class slackMessagesTest extends TestCase
{
    public function test_hello_alice(): void
    {
        $contents = file_get_contents(__DIR__."/messages/hello_alice.json");
        $event = json_decode($contents, true);
        $app = new App($event);
        $app->botSelectionName = "Alice";
        $bot = createNewBot($app);
        $result = onSlackEvent($app, $bot, $event, null);
        $this->assertNotSame($result, null);
    }
}
