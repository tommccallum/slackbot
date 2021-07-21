<?php


declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_StemmerTest extends TestCase
{
    public function test_stemmer(): void
    {
        $s = new Stemmer();
        $this->assertTrue($s->isConsonent("TOY", 0));
        $this->assertTrue($s->isConsonent("TOY", 1) == false);
        $this->assertTrue($s->isConsonent("TOY", 2));
        $this->assertTrue($s->isConsonent("SYZYGY", 0) == true);
        $this->assertTrue($s->isConsonent("SYZYGY", 1) == false);
        $this->assertTrue($s->isConsonent("SYZYGY", 2) == true);
        $this->assertTrue($s->isConsonent("SYZYGY", 3) == false);
        $this->assertTrue($s->isConsonent("SYZYGY", 4) == true);
        $this->assertTrue($s->measure("TR") == 0);
        $this->assertTrue($s->measure("EE") == 0);
        $this->assertTrue($s->measure("TREE") == 0);
        $this->assertTrue($s->measure("Y") == 0);
        $this->assertTrue($s->measure("BY") == 0);
        $this->assertTrue($s->measure("TROUBLE") == 1);
        $this->assertTrue($s->measure("OATS") == 1);
        $this->assertTrue($s->measure("TREES") == 1);
        $this->assertTrue($s->measure("IVY") == 1);
        $this->assertTrue($s->measure("TROUBLES") == 2);
        $this->assertTrue($s->measure("PRIVATE") == 2);
        $this->assertTrue($s->measure("OATEN") == 2);
        $this->assertTrue($s->measure("ORRERY") == 2);
        $this->assertTrue($s->step1a("caresses") == "caress");
        $this->assertTrue($s->step1a("ponies") == "poni");
        $this->assertTrue($s->step1a("ties") == "ti");
        $this->assertTrue($s->step1a("caress") == "caress");
        $this->assertTrue($s->step1a("cats") == "cat");

        $this->assertTrue($s->step1b("feed") == "feed");
        $this->assertTrue($s->step1b("agreed") == "agree");
        $this->assertTrue($s->step1b("plastered") == "plaster");
        $this->assertTrue($s->step1b("bled") == "bled");
        $this->assertTrue($s->step1b("motoring") == "motor");
        $this->assertTrue($s->step1b("sing") == "sing");

        $this->assertTrue($s->endsInDoubleConsonent("ess"));
        $this->assertTrue($s->endsInDoubleConsonent("es") == false);

        $this->assertTrue($s->step1b("conflated") == "conflate");
        $this->assertTrue($s->step1b("troubled") == "trouble");
        $this->assertTrue($s->step1b("sized") == "size");
        $this->assertTrue($s->step1b("hopping") == "hop");
        $this->assertTrue($s->step1b("tanned") == "tan");
        $this->assertTrue($s->step1b("falling") == "fall");
        $this->assertTrue($s->step1b("hissing") == "hiss");
        $this->assertTrue($s->step1b("fizzed") == "fizz");
        $this->assertTrue($s->step1b("failing") == "fail");
        $this->assertTrue($s->step1b("filing") == "file");

        $this->assertTrue($s->step1c("happy") == "happi");
        $this->assertTrue($s->step1c("sky") == "sky");


        $this->assertTrue($s->step3("triplicate") == "triplic");
        $this->assertTrue($s->step3("formative") == "form");
        $this->assertTrue($s->step3("formalize") == "formal");
        $this->assertTrue($s->step3("formalise") == "formal");
        $this->assertTrue($s->step3("electriciti") == "electric");
        $this->assertTrue($s->step3("electrical") == "electric");
        $this->assertTrue($s->step3("hopeful") == "hope");
        $this->assertTrue($s->step3("goodness") == "good");

        $this->assertTrue($s->step4("revival") == "reviv");
        $this->assertTrue($s->step4("allowance") == "allow");
        $this->assertTrue($s->step4("inference") == "infer");
        $this->assertTrue($s->step4("airliner") == "airlin");
        $this->assertTrue($s->step4("gyroscopic") == "gyroscop");
        $this->assertTrue($s->step4("adjustable") == "adjust");
        $this->assertTrue($s->step4("defensible") == "defens");
        $this->assertTrue($s->step4("irritant") == "irrit");
        $this->assertTrue($s->step4("replacement") == "replac");
        $this->assertTrue($s->step4("adjustment") == "adjust");
        $this->assertTrue($s->step4("dependent") == "depend");
        $this->assertTrue($s->step4("adoption") == "adopt");
        $this->assertTrue($s->step4("homologou") == "homolog");
        $this->assertTrue($s->step4("communism") == "commun");
        $this->assertTrue($s->step4("activate") == "activ");
        $this->assertTrue($s->step4("angulariti") == "angular");
        $this->assertTrue($s->step4("homologous") == "homolog");
        $this->assertTrue($s->step4("effective") == "effect");
        $this->assertTrue($s->step4("bowdlerize") == "bowdler");


        $this->assertTrue($s->step5a("probate") == "probat");
        $this->assertTrue($s->step5a("rate") == "rate");
        $this->assertTrue($s->step5a("cease") == "ceas");

        $this->assertTrue($s->step5b("controll") == "control");
        $this->assertTrue($s->step5b("roll") == "roll");
    }
}