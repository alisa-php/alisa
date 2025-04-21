<?php

use Alisa\Alisa;
use Alisa\Configuration;
use Alisa\Context;
use Alisa\Support\Render;

require __DIR__ . '/../vendor/autoload.php';

$config = new Configuration([
    'payload' => __DIR__ . '/payloads/intent.json',
]);

$alisa = new Alisa($config);

// $alisa->on(['request.command' => 'hello world'], function (Context $context) {
//     $context->enter('foo');
// });

// $alisa->onIntent(['MONSTER.SEARCH'], function (Context $context) {
//     $context->respond('MONSTER.SEARCH!!!!!');
// });

$alisa->onAny(function (Context $context) {
    $context->respond('any {pause:1000} {2: арбуз, арбуза, арбузов}');
});

// $alisa->onError(function (Context $context, Throwable $exception) {
//     $context->respond('[error] ' . $exception->getMessage());
// });

// $alisa->onScene('foo', function (Scene $scene) {
//     $scene->onEnter(function (Context $context) {
//         $context->respond('Какая у вас проблема?');
//     });

//     $scene->onAny(function (Context $context) {
//         $context->respond('scene foo');
//     });
// });

$alisa->dispatch();
