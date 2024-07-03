<?php

namespace Alisa\Support;

use Alisa\Yandex\Types\Button as SimpleButton;
use Alisa\Yandex\Types\Card\Button as CardButton;

class Buttons
{
    protected static array $buttons = [];

    public static function set(array $buttons): void
    {
        self::$buttons = $buttons;
    }

    public static function append(array $buttons): void
    {
        self::$buttons = [...self::$buttons, ...$buttons];
    }

    public static function add(string $alias, array $buttons): void
    {
        self::$buttons[$alias] = $buttons;
    }

    public static function get(string $alias, SimpleButton|CardButton|array $default = []): SimpleButton|CardButton|array
    {
        return self::$buttons[$alias] ?? $default;
    }

    public static function has(string $alias): bool
    {
        return isset($alias, self::$buttons);
    }

    public static function remove(string $alias): void
    {
        unset(self::$buttons[$alias]);
    }

    public static function clear(): void
    {
        self::$buttons = [];
    }

    public static function all(): array
    {
        return self::$buttons;
    }
}