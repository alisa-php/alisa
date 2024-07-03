<?php

namespace Alisa\Support\Concerns;

trait Collectable
{
    protected static array $items = [];

    public static function load(array $items): void
    {
        self::$items = $items;
    }

    public static function set(string $key, mixed $value): void
    {
        self::$items[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$items[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($key, self::$items);
    }

    public static function remove(string $key): void
    {
        unset(self::$items[$key]);
    }

    public static function increment(string $key, int $amount = 1): int
    {
        return self::get($key, 0) + $amount;
    }

    public static function decrement(string $key, int $amount = 1): int
    {
        return self::get($key, 0) - $amount;
    }

    public static function count(): int
    {
        return count(self::$items);
    }

    public static function all(): array
    {
        return self::$items;
    }
}