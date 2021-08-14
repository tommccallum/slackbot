<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class class_DialogueTest extends TestCase
{
    public function test_loadFromFile_Day0(): void
    {
        $path = __DIR__."/../responder/data/dialogues/day_0.json";
        $expected = [
            "channel_type" => "im",
            "text" => "This message should never be sent.  If it is then please inform the author.",
            "ts" => time() * 1000
        ];
        $dialog = new Dialogue();
        $dialog->loadFromFile($path);
        $this->assertFalse($dialog->match($expected));
    }

    public function test_loadFromFile_Day1000(): void
    {
        $path = __DIR__."/../responder/data/dialogues/day_1000.json";
        $expected = [
            "channel_type" => "im",
            "text" => "This future message should never be sent. Used for testing purposes only.",
            "ts" => time() * 1000
        ];
        $dialog = new Dialogue();
        $dialog->loadFromFile($path);
        $this->assertFalse($dialog->match($expected));
    }

    public function test_loadFromFile_Day9999(): void
    {
        $path = __DIR__."/../responder/data/dialogues/day_9999.json";
        $expected = [
            "channel_type" => "im",
            "text" => "Hi this is a test message.  Could you please reply to me?",
            "ts" => time() * 1000
        ];
        $dialog = new Dialogue();
        $dialog->loadFromFile($path);
        $this->assertTrue($dialog->match($expected));
    }
}
