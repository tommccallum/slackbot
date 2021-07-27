<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class class_LexicalAnalyserTest extends TestCase
{
    public function test_lexicalanalyser(): void
    {
        $la = new LexicalAnalysis();
        $str = "This is a string with an name Tom LO1.2.3.4! Fuzzilwig HTML html ::smiley:: 95.3 95 95.332 .234 U01CHRXLSUC";
        $words = splitStringIntoLexemes($str);
        $result = $la->inferPartsOfSpeechArray($words);
        $this->assertSame($result[7]['type'], "PERSON");
        $this->assertSame($result[8]['type'], "LEARNING_OUTCOME");
        $this->assertSame($result[13]['type'], "EMOJI");
        $this->assertSame($result[14]['type'], "NUMBER");
        $this->assertSame($result[15]['type'], "NUMBER");
        $this->assertSame($result[16]['type'], "NUMBER");
        $this->assertSame($result[17]['type'], "NUMBER");
        $this->assertSame($result[18]['type'], "SLACK_USER");
    }
}
