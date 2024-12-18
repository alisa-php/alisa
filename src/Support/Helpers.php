<?php

namespace Alisa\Support\Helpers;

use Alisa\Exceptions\AlisaException;
use Closure;

function array_flatten(array $array): array {
    ksort($array);

    return call_user_func_array('array_merge', $array);
}

function pipeline(array $callbacks, array $parameters): array {
    $next = function () use ($parameters, $callbacks, &$next) {
        static $index = 0;

        if (count($callbacks) > $index) {
            $callback = $callbacks[$index];
            $index++;

            $args = [...$parameters, $next];

            if (is_callable($callback)) {
                $callback(...$args);
            } else {
                (new ($callback))(...$args);
            }
        }
    };

    $next();

    return $parameters;
}

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

if (!function_exists('roll')) {
    /**
     * $chances = [
     *   ['%' => 10, 'result' => 'это вероятность 10%'],
     *   ['%' => 50, 'result' => 'это вероятность 50%'],
     *   ['%' => 4.5, 'result' => 'это вероятность 4.5%'],
     *   ['%' => 5.5, 'result' => 'это вероятность 5.5%'],
     *   ['%' => 30, 'result' => 'это вероятность 30%'],
     * ];
     *
     * @param array $chances
     * @return mixed
     * @throws Exception
     */
    function roll(array $chances): mixed
    {
        $sum = 0;
        foreach ($chances as $item) {
            $sum += (int)($item['%'] * 100);
        }

        if ($sum !== 10000) {
            throw new AlisaException("Сумма вероятностей должна быть равна 100, текущая сумма: " . ($sum / 100));
        }

        $random = mt_rand(1, 10000);
        $currentSum = 0;

        foreach ($chances as $item) {
            $currentSum += (int)($item['%'] * 100);
            if ($random <= $currentSum) {
                return $item['result'];
            }
        }

        // Если по какой-то причине мы дошли до этой точки, возвращаем случайный элемент
        return $chances[array_rand($chances)]['result'];
    }
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