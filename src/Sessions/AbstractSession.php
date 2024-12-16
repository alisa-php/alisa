<?php

namespace Alisa\Sessions;

abstract class AbstractSession
{
    protected static array $items = [];

    public static function load(array $items): void
    {
        static::$items = $items;
    }

    public static function set(string $key, string $value): void
    {
        static::$items[$key] = $value;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::$items[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($key, static::$items);
    }

    public static function remove(string $key): void
    {
        unset(static::$items[$key]);
    }

    public static function count(): int
    {
        return count(static::$items);
    }

    public static function all(): array
    {
        return static::toArray();
    }

    public static function toArray(): array
    {
        return static::$items;
    }
}