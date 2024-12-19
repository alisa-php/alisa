<?php

use Alisa\Alisa;
use Alisa\Context;
use Alisa\Sessions\Session;
use Alisa\Types\Button;

require __DIR__ . '/../vendor/autoload.php';

$alisa = new Alisa([
    'buttons' => [
        'incrementAndDecrement' => [
            new Button('-', 'decrement'),
            new Button('+', 'increment'),
        ],
    ],
]);

$alisa->onStart(function (Context $ctx) {
    $ctx->reply(
        'Счетчик: ' . Session::get('counter', 0),
        buttons: 'incrementAndDecrement'
    );
});

$alisa->onAction('increment', function (Context $ctx) {
    $ctx->reply(
        'Счетчик: ' . Session::increment('counter'),
        buttons: 'incrementAndDecrement'
    );
});

$alisa->onAction('decrement', function (Context $ctx) {
    $ctx->reply(
        'Счетчик: ' . Session::decrement('counter'),
        buttons: 'incrementAndDecrement'
    );
});

$alisa->run();