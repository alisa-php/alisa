<?php

use Alisa\Alisa;
use Alisa\Configuration;
use Alisa\Context;
use Alisa\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

$config = new Configuration([
    //
]);

$request = new Request(json_decode(file_get_contents(__DIR__ . '/payloads/command.json'), true));

$alisa = new Alisa($config, $request);

$alisa->listen(['foo' => 'bar'], function (Context $context) {
    $context->respond('foo === bar');
})->middleware(function (Context $context, Closure $next) {
    // $context->respond('[before] foo === bar');
    // $context->request->set('foo', 'BAAAAAAAR');
    $next($context);
    // $context->respond('[after] foo === bar');
});

$alisa->listen(['request.command' => 'hello world'], function (Context $context) {
    $context->respond('foo === baz');
})->middleware(function (Context $context, Closure $next) {
    // $context->respond('[before] foo === baz');
    $next($context);
    // $context->respond('[after] foo === baz');
});

$alisa->onFallback(function (Context $context) {
    $context->respond('fallback');
    dump('[fallback] context: ' . $context->request->get('foo'));
});

$alisa->onError(function (Context $context, Throwable $exception) {
    $context->respond('[error] ' . $exception->getMessage());
});

$alisa->dispatch();
