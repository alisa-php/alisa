<?php

namespace Alisa\Support;

use Closure;

class After
{
    protected static array $callbacks = [];

    public static function add(Closure|array|string $callback, array $arguments = [], int $priority = 0): void
    {
        self::$callbacks[$priority][] = compact('callback', 'arguments');
    }

    public static function run(): void
    {
        $callbacks = self::$callbacks;

        ksort($callbacks);

        $callbacks = array_merge(...array_values($callbacks));

        foreach ($callbacks as $callback) {
            execute($callback['callback'], ...$callback['arguments']);
        }
    }
}