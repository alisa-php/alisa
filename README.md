# Алиса

Эта библиотека позволяет легко и удобно создавать навыки для Алисы на языке PHP.

## Особенности

- Работает с PHP 8.4

## Установка

```bash
composer require alisa/alisa
```

## Использование

```php
use Alisa\Alisa;

$alisa = new Alisa;

$alisa->onStart(function (Context $context) {
    $context->respond('Привет!');
});

$alisa->onCommand('пока', function (Context $context) {
    $context->respond('Пока!');
});

$alisa->dispatch();
```

## Лицензия

MIT License
