<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once(__DIR__."/../responder/splitStringIntoLexemes.php");
require_once(__DIR__."/../responder/splitStringIntoClauses.php");
autoload_environment();

final class splitStringIntoClausesTest extends TestCase
{
    public function test_clauses(): void
    {   
        // $str = "This is a clause! This is a question? This is a statement. what is this";
        // $matches = splitStringIntoClauses($str);
        // var_dump($matches);
        
        // $str = "but however and or except although still yet though nevertheless";
        // $matches = splitStringIntoClauses($str);
        // var_dump($matches);

        $str = "Tom and John went to the park today.";
        $matches = splitStringIntoClauses($str);
        $this->assertSame(count($matches), 1);


        $str = "Tom went to the park and John went to the zoo today.";
        $matches = splitStringIntoClauses($str);
        $this->assertSame(count($matches), 2);

        $str = "recommend me a resource on security and honey";
        $matches = splitStringIntoClauses($str);
        $this->assertSame(count($matches), 1);


        $str = "Hi  U01CHRXLSUC , can you recommend a resource for security?";
        $matches = splitStringIntoClauses($str);
        $this->assertSame(count($matches), 2);
    }

    
}
