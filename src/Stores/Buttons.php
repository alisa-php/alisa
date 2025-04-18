<?php

namespace Alisa\Stores;

use Alisa\Types\Button;
use Alisa\Types\Card\Button as CardButton;

class Buttons extends AbstractStore
{
    protected static array $items = [];

    /**
     * @param string $alias
     * @param Button|CardButton|array $value
     * @return void
     */
    public static function set(string $alias, mixed $value): void
    {
        static::$items[$alias] = $value;
    }

    /**
     * @param string $alias
     * @param Button|CardButton|array $default
     * @return Button|CardButton|array
     */
    public static function get(string $alias, mixed $default = null): mixed
    {
        return static::$items[$alias] ?? $default;
    }
}