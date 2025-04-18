<?php

use Alisa\Alisa;
use Alisa\Context;
use Alisa\Scenes\Scene;
use Alisa\Scenes\Step;
use Alisa\Types\Button;
use App\Services\FeedbackService;
use App\Services\FormService;

require __DIR__.'/../vendor/autoload.php';

$alisa = new Alisa;

$alisa->onStart(function (Context $context) {
    $context->respond('Привет!');
});

$alisa->onFallback(function (Context $context) {
    $context->respond('Прости, я тебя не поняла');
});

$alisa->onCommand('обратная связь', function (Context $context) {
    $context->enter('feedback');
});

$alisa->onCommand('заполнить форму', function (Context $context) {
    $context->enter('form');
});

$alisa->onScene('feedback', function (Scene $scene) {
    $scene->onEnter(function (Context $context) {
        $context->respond('Какая у вас проблема?');
    });

    $scene->onAny(function (Context $context) {
        $context->session->set('text', $context->get('request.command'));

        $context->respond('Хорошо, мне отправить это сообщение поддержке?', buttons: [
            new Button('Да', 'yes'),
            new Button('Нет', 'no'),
        ]);
    });

    $scene->onAction('yes', function (Context $context) {
        FeedbackService::save($context->session->get('text'));
        $context->respond('Спасибо, ваше сообщение отправлено');
        $context->leave();
    });

    $scene->onAction('no', function (Context $context) {
        $context->respond('Всего доброго!');
        $context->leave();
    });
});

$alisa->onScene('form', function (Scene $scene) {
    $scene->onEnter(function (Context $context) {
        $context->nextStep('name');
    });

    $scene->onStep('name', function (Step $step) {
        $step->onBegin(function (Context $context) {
            $context->respond('Как вас зовут?');
        });

        $step->onAny(function (Context $context) {
            FormService::add('name', $context->get('request.command'));
            $context->nextStep('phone');
        });
    });

    $scene->onStep('phone', function (Step $step) {
        $step->onBegin(function (Context $context) {
            $context->respond('Укажите номер телефона');
        });

        $step->onAny(function (Context $context) {
            FormService::add('phone', $context->get('request.command'));
            $context->nextStep('email');
        });
    });

    $scene->onStep('email', function (Step $step) {
        $step->onBegin(function (Context $context) {
            $context->respond('Укажите адрес электронной почты');
        });

        $step->onAny(function (Context $context, FormService $formService) {
            $formService->add('email', $context->get('request.command'));
            $context->nextStep('finish');
        });
    });

    $scene->onStep('finish', function (Step $step) {
        $step->onBegin(function (Context $context) {
            $context->respond('Отправить форму?', buttons: [
                new Button('Да', 'yes'),
                new Button('Нет', 'no'),
            ]);
        });

        $step->onAction('yes', function (Context $context, FormService $formService) {
            $context->respond('Спасибо, форма отправлена');
            $context->leave();
            $formService->save();
        });

        $step->onAction('no', function (Context $context, FormService $formService) {
            $context->respond('Всего доброго!');
            $context->leave();
            $formService->cancel();
        });
    });
});

$alisa->dispatch();