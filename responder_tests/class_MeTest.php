<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_MeTest extends TestCase
{
    public function test_loadMe_exception(): void
    {
        $me = new Me();
        $this->expectException(\Exception::class);
        $me->loadFromFile("data/me.json");
    }

    public function test_loadMe_true(): void
    {
        $me = new Me();
        $me->loadFromFile(__DIR__."/../responder/data/personality.json");
        $this->assertSame($me->get("name"), "Alice");
    }
}