<?php

use Alisa\Alisa;
use Alisa\Configuration;
use Alisa\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

$config = new Configuration([
    //
]);

$alisa = new Alisa($config);

$request = new Request([
   'foo' => 'bar',
]);

$alisa->dispatch($request);