<?php

require_once("stemmer.php");

$s = new Stemmer();
assert($s->isConsonent("TOY", 0));
assert($s->isConsonent("TOY", 1) == false);
assert($s->isConsonent("TOY", 2));
assert($s->isConsonent("SYZYGY", 0) == true);
assert($s->isConsonent("SYZYGY", 1) == false);
assert($s->isConsonent("SYZYGY", 2) == true);
assert($s->isConsonent("SYZYGY", 3) == false);
assert($s->isConsonent("SYZYGY", 4) == true);
assert($s->measure("TR") == 0);
assert($s->measure("EE") == 0);
assert($s->measure("TREE") == 0);
assert($s->measure("Y") == 0);
assert($s->measure("BY") == 0);
assert($s->measure("TROUBLE") == 1);
assert($s->measure("OATS") == 1);
assert($s->measure("TREES") == 1);
assert($s->measure("IVY") == 1);
assert($s->measure("TROUBLES") == 2);
assert($s->measure("PRIVATE") == 2);
assert($s->measure("OATEN") == 2);
assert($s->measure("ORRERY") == 2);
assert($s->step1a("caresses") == "caress");
assert($s->step1a("ponies") == "poni");
assert($s->step1a("ties") == "ti");
assert($s->step1a("caress") == "caress");
assert($s->step1a("cats") == "cat");

assert($s->step1b("feed") == "feed");
assert($s->step1b("agreed") == "agree");
assert($s->step1b("plastered") == "plaster");
assert($s->step1b("bled") == "bled");
assert($s->step1b("motoring") == "motor");
assert($s->step1b("sing") == "sing");

assert($s->endsInDoubleConsonent("ess"));
assert($s->endsInDoubleConsonent("es") == false);

assert($s->step1b("conflated") == "conflate");
assert($s->step1b("troubled") == "trouble");
assert($s->step1b("sized") == "size");
assert($s->step1b("hopping") == "hop");
assert($s->step1b("tanned") == "tan");
assert($s->step1b("falling") == "fall");
assert($s->step1b("hissing") == "hiss");
assert($s->step1b("fizzed") == "fizz");
assert($s->step1b("failing") == "fail");
assert($s->step1b("filing") == "file");

assert($s->step1c("happy") == "happi");
assert($s->step1c("sky") == "sky");


assert($s->step3("triplicate") == "triplic");
assert($s->step3("formative") == "form");
assert($s->step3("formalize") == "formal");
assert($s->step3("formalise") == "formal");
assert($s->step3("electriciti") == "electric");
assert($s->step3("electrical") == "electric");
assert($s->step3("hopeful") == "hope");
assert($s->step3("goodness") == "good");

assert($s->step4("revival") == "reviv");
assert($s->step4("allowance") == "allow");
assert($s->step4("inference") == "infer");
assert($s->step4("airliner") == "airlin");
assert($s->step4("gyroscopic") == "gyroscop");
assert($s->step4("adjustable") == "adjust");
assert($s->step4("defensible") == "defens");
assert($s->step4("irritant") == "irrit");
assert($s->step4("replacement") == "replac");
assert($s->step4("adjustment") == "adjust");
assert($s->step4("dependent") == "depend");
assert($s->step4("adoption") == "adopt");
assert($s->step4("homologou") == "homolog");
assert($s->step4("communism") == "commun");
assert($s->step4("activate") == "activ");
assert($s->step4("angulariti") == "angular");
assert($s->step4("homologous") == "homolog");
assert($s->step4("effective") == "effect");
assert($s->step4("bowdlerize") == "bowdler");


assert($s->step5a("probate") == "probat");
assert($s->step5a("rate") == "rate");
assert($s->step5a("cease") == "ceas");

assert($s->step5b("controll") == "control");
assert($s->step5b("roll") == "roll");
