<?php

use Alisa\Alisa;
use Alisa\Configuration;
use Alisa\Context;

require __DIR__ . '/../vendor/autoload.php';

$config = new Configuration;

$alisa = new Alisa($config);

$alisa->onAny(function (Context $context) {
    $context->respond('Привет!');
});

$alisa->dispatch();
