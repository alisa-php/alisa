<?php

use Alisa\Alisa;
use Alisa\Context;

require __DIR__ . '/../vendor/autoload.php';

$alisa = new Alisa;

$alisa->onStart(function (Context $ctx) {

});

$alisa->onAny(function (Context $ctx) {

});

$alisa->run();