<?php

namespace Alisa\Support;

class Asset
{
    protected static array $assets = [];

    public static function load(array $assets): void
    {
        self::$assets = $assets;
    }

    public static function set(string $alias, string $value): void
    {
        self::$assets[$alias] = $value;
    }

    public static function get(string $alias, ?string $default = null): ?string
    {
        return self::$assets[$alias] ?? $default;
    }

    public static function has(string $alias): bool
    {
        return isset($alias, self::$assets);
    }

    public static function remove(string $alias): void
    {
        unset(self::$assets[$alias]);
    }

    public static function all(): array
    {
        return self::$assets;
    }
}