<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once(__DIR__."/../responder/splitStringIntoLexemes.php");
autoload_environment();

final class splitStringIntoLexemesTest extends TestCase
{
    public function test_learningoutcome(): void
    {   
        $str = "LO1.2.3.4 and 2.3.4.1 was a learning outcome, ::100:: and ::smiley_face:: so was lo1.2.3.4 and so was lo 1.2.3.4 and learning outcome 1.3.2.4. 95 95.3 95.345 .234 U01CHRXLSUC";
        $matches = splitStringIntoLexemes($str);
        // var_dump($matches);
        $this->assertSame($matches[0], "LO1.2.3.4");
        $this->assertSame($matches[2], "2.3.4.1");
        $this->assertSame($matches[13], "lo1.2.3.4");
        $this->assertSame($matches[17], "lo 1.2.3.4");
        $this->assertSame($matches[21], "1.3.2.4");
        $this->assertSame($matches[23], "95");
        $this->assertSame($matches[24], "95.3");
        $this->assertSame($matches[25], "95.345");
        $this->assertSame($matches[26], ".234");
        $this->assertSame($matches[27], "U01CHRXLSUC");
    }


}