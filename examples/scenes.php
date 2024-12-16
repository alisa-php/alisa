<?php

use Alisa\Alisa;
use Alisa\Context;
use Alisa\Scenes\Scene;
use Alisa\Sessions\Session;

require __DIR__ . '/../vendor/autoload.php';

$alisa = new Alisa([
    'request' => json_decode(file_get_contents(__DIR__ . '/requests/scene.json'), true),

    'scene' => [
        'driver' => Session::class,
        'key' => '__scene__',
    ],
]);

$alisa->on(['request.command' => 'hello world'], function (Context $context) {
    dump('base ok');
});

$alisa->onFallback(function (Context $context) {
    dump('base fallback');
});

$scene = new Scene('foo');

$scene->on(['request.command' => 'hello world'], function (Context $context) use ($scene) {
    dump('foo ok');
});

$scene->onFallback(function (Context $context) {
    dump('foo fallback');
});

$alisa->run();