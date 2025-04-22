<?php

use Alisa\Alisa;
use Alisa\Configuration;
use Alisa\Context;
use Alisa\Types\Button;

require __DIR__ . '/../vendor/autoload.php';

$config = new Configuration([
    // 'payload' => __DIR__ . '/payloads/action.json',
    'buttons' => [
        'incrementAndDecrement' => [
            new Button('-', 'decrement'),
            new Button('+', 'increment'),
        ],
    ],
]);

$alisa = new Alisa($config);

$alisa->onStart(function (Context $context) {
    $context->respond(
        'Счетчик: ' . $context->session->get('counter', 0),
        buttons: 'incrementAndDecrement'
    );
});

$alisa->onAction('increment', function (Context $context) {
    $context->respond(
        'Счетчик: ' . $context->session->increment('counter'),
        buttons: 'incrementAndDecrement'
    );
});

$alisa->onAction('decrement', function (Context $context) {
    $context->respond(
        'Счетчик: ' . $context->session->decrement('counter'),
        buttons: 'incrementAndDecrement'
    );
});

$alisa->dispatch();