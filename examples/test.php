<?php

use Alisa\Alisa;
use Alisa\Configuration;
use Alisa\Context;
use Alisa\Scenes\Scene;

require __DIR__ . '/../vendor/autoload.php';

$config = new Configuration([
    'payload' => __DIR__ . '/payloads/command.json',
]);

$alisa = new Alisa($config);

// $alisa->on(['request.command' => 'hello world'], function (Context $context) {
//     $context->enter('foo');
// });

// $alisa->onIntent(['MONSTER.SEARCH'], function (Context $context) {
//     $context->respond('MONSTER.SEARCH!!!!!');
// });

$alisa->onAny(function (Context $context) {
    $context->respond('any {tts: Привет} {pause:1000} {2: арбуз, арбуза, арбузов}');
});

// $alisa->onError(function (Context $context, Throwable $exception) {
//     $context->respond('[error] ' . $exception->getMessage());
// });

$alisa->middleware(function ($c, $next) {
    dump('global before');
    $next($c);
    dump('global after');
});

$alisa->onScene('foo', function (Scene $scene) {
    $scene->onEnter(function (Context $context) {
        $context->respond('Какая у вас проблема?');
    });

    $scene->onAny(function (Context $context) {
        $context->respond('scene foo');
    });

    $scene->onFallback(function (Context $context) {
        $context->respond('scene foo fallback');
    });
})->middleware(function ($c, $next) {
    dump('scene foo before');
    $next($c);
    dump('scene foo after');
});

$alisa->dispatch();
