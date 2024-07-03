# Alisa

Библиотека для разработки навыков голосового помощника Яндекс Алиса (Диалоги).

# Установка

```bash
composer require alisa/alisa
```

# Примеры

Пример навыка, который повторяет все что скажет пользователь.

```php
use Alisa\Alisa;
use Alisa\Context;

$alisa = new Alisa;

$alisa->onStart(function (Context $context) {
    $context->reply('Привет, я повторяшка!');
});

$alisa->onAny(function (Context $context) {
    $context->reply($context('request.command'));
});

$alisa->run();
```

# Документация

## События

С помощью событий можно удобно отлавливать и обрабатывать входящие запросы от Диалогов.

### `on()`

```php
on(Closure|array $pattern, Closure|array|string $handler, int $priority = 0): Event
```

Это самый базовый и универсальный способ отлова событий, на нем строятся все методы событий, такие как `onStart`, `onCommand` и другие.

Событие можно отловить разным способом:
- `on('request.command')` - ловим все события где есть ключ `request.command`;
- `on(['request.command' => 'привет'])` - ловим события где `команда === привет`;
- `on(['request.command' => '/прив/iu'])` - regex, ловим события где может быть `привет`, `приветствую`, `привееет` и т.п.;
- `on('request.command' => 'привет {name}'])` - ловим событие где в комманде есть `привет вася` или любое другое слово вместо `вася`.
- `on('request.command' => 'привет {name?}'])` - работает как пример выше, но в данном случае мы поймаем как `привет вася`, так и просто `привет`, потому что `{name?}` - это опциональное слово.
- `on(fn (Context $context) => ...)` - если результат функции `true` - есть мэтч, если `false` - мэтча нет соответсвенно, это самый гибкий вариант отлова если нужно что-то нестандартное;

**Примеры использования ниже:**

```php
$alisa->on('request.command', function (Context $context) { ... });
```

В примере ниже мы обработаем событие если это `request.command` **ИЛИ** `session.new`.

```php
$alisa->on(['request.command', 'session.new'], function (Context $context) { ... });
```

```php
$alisa->on(['request.command' => 'привет'], function (Context $context) { ... });
```

```php
$alisa->on(['request.command' => '/прив/iu'], function (Context $context) { ... });
```

В примере ниже, в функцию обработчик `{name}` мы принимаем вторым параметром.

```php
$alisa->on(['request.command' => 'привет {name}'], function (Context $context, string $name) { ... });
```

```php
$alisa->on(['request.command' => 'привет {name?}'], function (Context $context, ?string $name = null) { ... });
```

Мы можем добавить `{user}` `{time?}` столько, сколько нам нужно, но главное, опциональное слово всегда должно быть после обязательного.

- `/ban {user} {time?}` - OK;
- `/ban {user?} {time?}` - OK;
- `/ban {user?} {time}` - НЕ ОК;

**Как работает пример ниже:**

- Если сообщение `/ban vasya 30` - поймает `$name = vasya`, `$time = 30`.
- Если сообщение `/ban vasya 13 37` - поймает `$name = vasya`, `$time = 13 37`.

```php
$alisa->on(
    ['request.command' => '/ban {user} {time?}'],
    function (Context $context, string $user, ?string $name = null) {
        //
    }
);
```

Вы также можете ловить слова с помощью regex:

```php
$alisa->on(
    ['request.command' => '/привет (.+?)/iu'],
    function (Context $context, string $name) {
        //
    }
);
```

А еще, вы можете комбинировать разные варианты в одном событии.

Каждое перечисление раценивается как **ИЛИ**.

```php
$alisa->on([
    'request.command' => ['/прив/iu', 'привет {name?}' 'hello', 'hola'],
    function (Context $context, string $name) {
        //
    }
])
```

Так тоже можно:

```php
$alisa->on([
    'request.command' => ['/прив/iu', 'привет {name?}' 'hello', 'hola'],
    'state.session.foo' => ['bar'],
    'state.session.lorem',
    fn (Context $context) => 2 + 2 === 4,
    function (Context $context, ?string $name = null) {
        //
    }
])
```
