<?php

namespace Alisa\Stores;

abstract class AbstractStore
{
    protected static array $items = [];

    public static function load(array $items): void
    {
        self::$items = $items;
    }

    public static function set(string $alias, string $value): void
    {
        self::$items[$alias] = $value;
    }

    public static function get(string $alias, ?string $default = null): ?string
    {
        return self::$items[$alias] ?? $default;
    }

    public static function has(string $alias): bool
    {
        return isset($alias, self::$items);
    }

    public static function remove(string $alias): void
    {
        unset(self::$items[$alias]);
    }

    public static function all(): array
    {
        return self::toArray();
    }

    public static function toArray(): array
    {
        return self::$items;
    }
}