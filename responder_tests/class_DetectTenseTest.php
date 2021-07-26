<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_DetectTenseTest extends TestCase
{
    public function test_detecttense_test1(): void
    {
        $str = "The sun rises in the East.";
        $o = new DetectTense($str);
        $s = $o->state();
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 1);
        $this->assertSame($s['future'], 0);
        $this->assertSame($s['simple'], 1);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 0);
    }

    public function test_detecttense_test2(): void
    {
        $str = "Rita goes to school.";
        $o = new DetectTense($str);
        $s = $o->state();
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 1);
        $this->assertSame($s['future'], 0);
        $this->assertSame($s['simple'], 1);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 0);
    }



    public function test_detecttense_test3(): void
    {
        $str = "I was eating pudding.";
        $o = new DetectTense($str);
        $s = $o->state();
        $this->assertSame($s['past'], 1);
        $this->assertSame($s['present'], 0);
        $this->assertSame($s['future'], 0);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 1);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 0);
    }

    public function test_detecttense_test4(): void
    {
        $str = "Sia was writing a letter to the editor.";
        $o = new DetectTense($str);
        $s = $o->state();
        $this->assertSame($s['past'], 1);
        $this->assertSame($s['present'], 0);
        $this->assertSame($s['future'], 0);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 1);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 0);
    }


    public function test_detecttense_test5(): void
    {
        $str = "He has just eaten food";
        $o = new DetectTense($str);
        $s = $o->state();
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 1);
        $this->assertSame($s['future'], 0);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 1);
        $this->assertSame($s['perfectContinuous'], 0);
    }

    public function test_detecttense_test6(): void
    {
        $str = "I have just read the book";
        $o = new DetectTense($str);
        $s = $o->state();
        
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 1);
        $this->assertSame($s['future'], 0);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 1);
        $this->assertSame($s['perfectContinuous'], 0);
    }




    public function test_detecttense_test7(): void
    {
        $str = "I have been cleaning regularly since Monday";
        $o = new DetectTense($str);
        $s = $o->state();
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 1);
        $this->assertSame($s['future'], 0);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 1);
    }

    public function test_detecttense_test8(): void
    {
        $str = "SHe has been using the night cream for several months";
        $o = new DetectTense($str);
        $s = $o->state();
        
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 1);
        $this->assertSame($s['future'], 0);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 1);
    }







    public function test_detecttense_test9(): void
    {
        $str = "I shall go to school tomorrow";
        $o = new DetectTense($str);
        $s = $o->state();
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 0);
        $this->assertSame($s['future'], 1);
        $this->assertSame($s['simple'], 1);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 0);
    }

    public function test_detecttense_test10(): void
    {
        $str = "My mother will feed me";
        $o = new DetectTense($str);
        $s = $o->state();
        
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 0);
        $this->assertSame($s['future'], 1);
        $this->assertSame($s['simple'], 1);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 0);
    }





    public function test_detecttense_test11(): void
    {
        $str = "He shall be writing his exam";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 0);
        $this->assertSame($s['future'], 1);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 1);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 0);
    }

    public function test_detecttense_test12(): void
    {
        $str = "We will be going to the zoo";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 0);
        $this->assertSame($s['future'], 1);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 1);
        $this->assertSame($s['perfect'], 0);
        $this->assertSame($s['perfectContinuous'], 0);
    }






    public function test_detecttense_test13(): void
    {
        $str = "I shall have started writing by that time";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 0);
        $this->assertSame($s['future'], 1);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 1);
        $this->assertSame($s['perfectContinuous'], 0);
    }

    public function test_detecttense_test14(): void
    {
        $str = "We will have reached Goa by then";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['past'], 0);
        $this->assertSame($s['present'], 0);
        $this->assertSame($s['future'], 1);
        $this->assertSame($s['simple'], 0);
        $this->assertSame($s['continuous'], 0);
        $this->assertSame($s['perfect'], 1);
        $this->assertSame($s['perfectContinuous'], 0);
    }


    // NOTE these are known to be faulty and return continuous not perfectContinuous
    // public function test_detecttense_test15(): void
    // {
    //     $str = "By next year we will be graduating";
    //     $o = new DetectTense($str);
    //     $s = $o->state();
    //     #var_dump($str);
    //     #var_dump($s);
        
    //     $this->assertSame($s['past'], 0);
    //     $this->assertSame($s['present'], 0);
    //     $this->assertSame($s['future'], 1);
    //     $this->assertSame($s['simple'], 0);
    //     $this->assertSame($s['continuous'], 0);
    //     $this->assertSame($s['perfect'], 0);
    //     $this->assertSame($s['perfectContinuous'], 1);
    // }

    // public function test_detecttense_test16(): void
    // {
    //     $str = "They shall be serving food in the slum area tomorrow";
    //     $o = new DetectTense($str);
    //     $s = $o->state();
    //     #var_dump($str);
    //     #var_dump($s);
        
    //     $this->assertSame($s['past'], 0);
    //     $this->assertSame($s['present'], 0);
    //     $this->assertSame($s['future'], 1);
    //     $this->assertSame($s['simple'], 0);
    //     $this->assertSame($s['continuous'], 0);
    //     $this->assertSame($s['perfect'], 0);
    //     $this->assertSame($s['perfectContinuous'], 1);
    // }



    public function test_detectvoice_test1(): void
    {
        $str = "John threw the ball";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "ACTIVE");

        $str = "The ball was thrown";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "PASSIVE");

        $str = "The ball was thrown by John";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "PASSIVE");
    }
    
    public function test_detectvoice_test2(): void
    {
        
        $str = "The ball hit Bob";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "ACTIVE");

        $str = "Bob was hit by the ball";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "PASSIVE");

        $str = "Bob got hit by the ball";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "PASSIVE");
    }


    public function test_detectvoice_test3(): void
    {
        $str = "The food is being served";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "PASSIVE");
    }


    public function test_detectvoice_test4(): void
    {
        $str = "The stadium will have been built by next January";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "PASSIVE");
    }

    public function test_detectvoice_test5(): void
    {
        $str = "I would have gotten injured";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "PASSIVE");
    }

    public function test_detectvoice_test6(): void
    {
        $str = "It isn't nice to be insulted";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "PASSIVE");
    }

    public function test_detectvoice_test7(): void
    {
        $str = "Having been humiliated, he left the stage.";
        $o = new DetectTense($str);
        $s = $o->state();
        #var_dump($str);
        #var_dump($s);
        
        $this->assertSame($s['voice'], "PASSIVE");
    }

}