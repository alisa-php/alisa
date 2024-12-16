<?php

use Alisa\Alisa;
use Alisa\Context;
use Alisa\Support\Buttons;
use Alisa\Yandex\Sessions\Session;
use Alisa\Yandex\Types\Button;

require __DIR__ . '/../vendor/autoload.php';

$alisa = new Alisa;

Buttons::set('incrementing', [
    new Button('+1', 'increment:1', hide: true),
    new Button('+5', 'increment:5', hide: true),
    new Button('+10', 'increment:10', hide: true),
    new Button('+25', 'increment:25', hide: true),
]);

$alisa->onStart(function (Context $context) {
    $context->reply('Счётчик: 0', buttons: 'incrementing');
});

$alisa->onAction('increment:{amount}', function (Context $context, int $amount) {
    $value = Session::increment('counter', $amount);

    $context->reply('Счётчик: ' .  $value, buttons: 'incrementing');
});

$alisa->run();