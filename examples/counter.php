<?php

use Alisa\Alisa;
use Alisa\Context;
use Alisa\Support\Buttons;
use Alisa\Yandex\Sessions\Session;
use Alisa\Yandex\Types\Button;

require __DIR__ . '/../vendor/autoload.php';

$alisa = new Alisa;

Buttons::add('incrementing', [
    new Button('+1', 'increment:1', hide: true),
    new Button('+5', 'increment:5', hide: true),
    new Button('+10', 'increment:10', hide: true),
    new Button('+25', 'increment:25', hide: true),
]);

$alisa->onStart(function (Context $context) {
    $context->reply('Счётчик: 0', buttons: 'incrementing');
});

$alisa->onAction('increment:{amount}', function (Context $context, int $amount) {
    $counter = Session::increment('counter', $amount);

    Session::set('counter', $counter);

    $context->reply('Счётчик: ' .  $counter, buttons: 'incrementing');
});

$alisa->run();