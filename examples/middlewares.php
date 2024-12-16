<?php

use Alisa\Alisa;
use Alisa\Context;

require __DIR__ . '/../vendor/autoload.php';

$alisa = new Alisa([
    'request' => json_decode(file_get_contents(__DIR__ . '/requests/command.json'), true),
]);

$alisa->middleware([
    function (Context $context, Closure $next) {
        dump('[global] middleware before: 1');
        $next($context);
        dump('[global] middleware after: 1');
    },
    function (Context $context, Closure $next) {
        dump('[global] middleware before: 2');
        $next($context);
        dump('[global] middleware after: 2');
    },
]);

$alisa->on(['request.command' => 'hello world'], function (Context $context) {
    dump('ok');
})->middleware([
    function (Context $context, Closure $next) {
        dump('[on] middleware before: 1');
        $next($context);
        dump('[on] middleware after: 1');
    },
    function (Context $context, Closure $next) {
        dump('[on] middleware before: 2');
        $next($context);
        dump('[on] middleware after: 2');
    },
]);

$alisa->onFallback(function (Context $context) {
    dump('fallback');
});

$alisa->run();