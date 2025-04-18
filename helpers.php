<?php

use Alisa\Support\After;
use Alisa\Support\Pipeline;

/**
 * Позволяет последовательно применять цепочку функций к входному значению (пайплайн).
 *
 * Поддерживает:
 * - Имена функций ('trim', 'strtoupper', и т.п.)
 * - Callable-массивы ([Класс::class, 'метод'], [$объект, 'метод'])
 * - Анонимные функции (Closure)
 * - Invokable-объекты (объекты с методом __invoke())
 *
 * @param array $callbacks Массив функций для последовательного вызова.
 *     Каждый элемент может быть:
 *     - string (имя функции)
 *     - array ([класс, метод] или [$объект, метод])
 *     - callable (Closure или invokable-объект)
 *
 * @param mixed $input Входное значение, которое будет передано в первую функцию.
 *
 * @return mixed Результат обработки цепочки функций.
 *
 * @throws InvalidArgumentException Если передан не callable элемент.
 *
 * ```
 * // Простой пайплайн со строковыми функциями
 * pipeline(['trim', 'strtoupper'], '  hello  '); // "HELLO"
 * ```
 *
 * ```
 * // С использованием методов класса
 * pipeline(
 *     ['trim', [Formatter::class, 'format'], 'strtoupper'],
 *     '  text  '
 * );
 * ```
 *
 * ```
 * // С анонимной функцией и передачей $next
 * pipeline([
 *     fn($x) => trim($x),
 *     function ($x, $next) {
 *         return $next(strtoupper($x));
 *     }
 * ], '  hello  ');
 * ```
 */
function pipeline(array $callbacks, mixed $input) {
    return Pipeline::make($callbacks)->process($input);
}

/**
 * Вызывает указанный обработчик с переданными параметрами.
 *
 * Поддерживает различные форматы обработчиков:
 * - Замыкание (Closure): вызывается напрямую
 * - Имя класса (string): создается экземпляр и вызывается метод __invoke
 * - Массив из двух элементов [класс, метод]: создается экземпляр и вызывается указанный метод
 * - Массив из одного элемента [класс]: создается экземпляр и вызывается метод __invoke
 * - Объект с методом __invoke: вызывается метод __invoke
 *
 * @param mixed $handler Обработчик, который нужно вызвать
 * @param mixed ...$parameters Параметры для передачи обработчику
 * @return mixed Результат вызова обработчика
 * @throws InvalidArgumentException Если обработчик не может быть вызван
 */
function execute($handler, ...$parameters): mixed {
    if ($handler instanceof Closure || method_exists($handler, '__invoke')) {
        return $handler(...$parameters);
    }

    if (is_string($handler)) {
        if (class_exists($handler)) {
            return (new $handler)(...$parameters);
        }
        if (function_exists($handler)) {
            return $handler(...$parameters);
        }
    }

    if (is_array($handler)) {
        if (count($handler) === 2) {
            [$class, $method] = $handler;
            if (is_object($class)) {
                return $class->$method(...$parameters);
            }
            return (new $class)->$method(...$parameters);
        }

        if (count($handler) === 1) {
            return (new $handler[0])(...$parameters);
        }
    }

    throw new InvalidArgumentException('Невозможно выполнить вызов обработчика');
}

function plural(float|int $count, array $forms): string
{
    $one = $count % 10 == 1 && $count % 100 != 11 ? 0 : ($count % 10 >= 2 && $count % 10 <= 4 && ($count % 100 < 10 || $count % 100 >= 20) ? 1 : 2);

    return $count . ' ' . $forms[$one];
}