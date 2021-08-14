<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class class_DialogueCollectionTest extends TestCase
{
    public function test_load_all(): void
    {
        $collection = new DialogueCollection();
        $collection->loadFromDirectory(__DIR__."/../responder/data/dialogues");
        $this->assertSame($collection->length(), 3);
    }

    public function test_getMatchingDateTime(): void
    {
        $collection = new DialogueCollection();
        $collection->loadFromDirectory(__DIR__."/../responder/data/dialogues");
        $dialogs = $collection->getMatchingDateTime(date("Y-m-d"), "00:00:00");
        $this->assertSame(count($dialogs), 1);
    }

    public function test_matchSlackMessage(): void
    {
        $expected = [
            "channel_type" => "im",
            "text" => "This message should never be sent.  If it is then please inform the author.",
            "ts" => time() * 1000
        ];

        $collection = new DialogueCollection();
        $collection->loadFromDirectory(__DIR__."/../responder/data/dialogues");
        $found = $collection->matchSlackMessage($expected);
        $this->assertSame($found, true);
    }
}
