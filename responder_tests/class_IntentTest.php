<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_IntentTest extends TestCase
{
    public function test_match(): void
    {
        $str = "Hi U1234567890! How are you?";
        $clause = splitStringIntoClauses($str);
        // var_dump($clause);
        $intent = new Intent();
        $example = "Hi ?u:user";
        $exampleAST = $intent->parseExample($example);
        // var_Dump($exampleAST);
        $match = $intent->matchExampleToClause($exampleAST, $clause[0]);
        // var_dump($match);
        $this->assertSame(count($match['matches']),2);
        $this->assertSame($match['quality'],1);
    }

    public function test_match_fail(): void
    {
        $str = "U1234567890! How are you?";
        $clause = splitStringIntoClauses($str);
        // var_dump($clause);
        $intent = new Intent();
        $example = "*Hi ?u:user";
        $exampleAST = $intent->parseExample($example);
        // var_Dump($exampleAST);
        $match = $intent->matchExampleToClause($exampleAST, $clause[0]);
        // var_dump($match);
        $this->assertSame($match,false);
    }

    public function test_match_loose_pass(): void
    {
        $str = "U1234567890! How are you?";
        $clause = splitStringIntoClauses($str);
        // var_dump($clause);
        $intent = new Intent();
        $example = "Hi ?u:user";
        $exampleAST = $intent->parseExample($example);
        // var_Dump($exampleAST);
        $match = $intent->matchExampleToClause($exampleAST, $clause[0]);
        // var_dump($match);
        $this->assertSame(count($match['matches']),1);
        $this->assertSame($match['quality'],0.5);
    }

    public function test_match_loose_pass_2(): void
    {
        $str = "recommend me a resource on security and honey";
        $clause = splitStringIntoClauses($str);
        // var_dump($clause);
        $intent = new Intent();
        $example =  "can you *recommend a *resource for ?topic:any:*";
        $exampleAST = $intent->parseExample($example);
        // var_dump($exampleAST);
        $match = $intent->matchExampleToClause($exampleAST, $clause[0]);
        // var_dump($match);
        $this->assertSame(count($match['matches']),3);
    }

    public function test_match_loose_pass_3(): void
    {
        $str = "any html suggestions";
        $clause = splitStringIntoClauses($str);
        // var_dump($clause);
        $intent = new Intent();
        $example =  "*any ?topic:any:* suggestions";
        $exampleAST = $intent->parseExample($example);
        // var_dump($exampleAST);
        $match = $intent->matchExampleToClause($exampleAST, $clause[0]);
        // var_dump($match);
        $this->assertSame(count($match['matches']),3);
    }

   
}