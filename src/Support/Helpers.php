<?php

namespace Alisa\Support\Helpers;
use Alisa\Exceptions\AlisaException;
use Closure;

function plural(float|int $count, array $forms): string
{
    $one = $count % 10 == 1 && $count % 100 != 11 ? 0 : ($count % 10 >= 2 && $count % 10 <= 4 && ($count % 100 < 10 || $count % 100 >= 20) ? 1 : 2);

    return $count . ' ' . $forms[$one];
}

function mb_ucfirst(string $string, ?string $encoding = null): string {
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, null, $encoding);

    return mb_strtoupper($firstChar, $encoding) . $then;
}

function array_flatten(array $array): array {
    ksort($array);

    return call_user_func_array('array_merge', $array);
}

function call_handler($handler, ...$parameters): mixed {
    if ($handler instanceof Closure) {
        return call_user_func($handler, ...$parameters);
    } else if (is_string($handler)) {
        return (new $handler)(...$parameters);
    } else if (is_array($handler)) {
        if (count($handler) === 2) {
            [$class, $method] = $handler;

            $handler = new $class;
            return $handler->{$method}(...$parameters);
        } else {
            return (new $handler[0])(...$parameters);
        }
    }

    throw new AlisaException('Невозможно выполнить вызов', 1001);
}