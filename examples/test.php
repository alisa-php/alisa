<?php

use Alisa\Alisa;
use Alisa\Configuration;
use Alisa\Context;
use Alisa\Http\Request;
use Alisa\Scenes\Scene;

require __DIR__ . '/../vendor/autoload.php';

$config = new Configuration([
    //
]);

$request = new Request(json_decode(file_get_contents(__DIR__ . '/payloads/command.json'), true));

$alisa = new Alisa($config, $request);

$alisa->listen(['request.command' => 'hello world'], function (Context $context) {
    $context->enter('foo');
});

$alisa->onError(function (Context $context, Throwable $exception) {
    $context->respond('[error] ' . $exception->getMessage());
});

$alisa->onScene('foo', function (Scene $scene) {
    $scene->onEnter(function (Context $context) {
        $context->respond('Какая у вас проблема?');
    });

    $scene->onAny(function (Context $context) {
        $context->respond('scene foo');
    });
});

$alisa->dispatch();
