<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once(__DIR__."/../responder/traverseMessageBlocks.php");
autoload_environment();

final class traverseMessageBlocksTest extends TestCase
{
    public function test_walkMessageBlocks(): void
    {   
        $testCaseContents = file_get_contents(__DIR__."/messages/complex_message_1.json");
        $json = json_decode($testCaseContents, true);
        // var_dump($json);
        // $resultArray = walk_message_blocks($json, "getTextBlocks");
        // var_dump($resultArray);
        
        // $text = collapseTextBlocksIntoString($resultArray);
        // var_dump($text);

        // $resultArray = walk_message_blocks($json, "getEmojiBlocks");
        // var_dump($resultArray);

        $this->assertSame(1,1);
    }
}