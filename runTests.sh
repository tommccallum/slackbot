#!/bin/bash

./phpunit --bootstrap common/autoload.php common_tests
#./phpunit --bootstrap common/autoload.php webclient_tests
./phpunit --bootstrap responder/autoload.php responder_tests
