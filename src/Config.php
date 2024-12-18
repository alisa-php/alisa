<?php

namespace Alisa;

use Alisa\Sessions\Session;

class Config
{
    protected static array $config = [
        'skill_id' => null,
        'oauth' => null,

        'storage_path' => null,

        'request' => null,

        'middlewares' => [],
        'components' => [],
        'assets' => [],
        'buttons' => [],

        'scene' => [
            'driver' => Session::class,
            'key' => '__scene__',
        ],
    ];

    public static function fill(array $config = []): void
    {
        static::$config = array_replace_recursive(static::$config, $config);
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);

        $value = static::$config;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return static
     */
    public static function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);

        $reference = &static::$config;

        foreach ($segments as $segment) {
            if (!isset($reference[$segment]) || !is_array($reference[$segment])) {
                $reference[$segment] = [];
            }

            $reference = &$reference[$segment];
        }

        $reference = $value;
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        $segments = explode('.', $key);

        $value = static::$config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    /**
     * @param mixed $key
     * @return static
     */
    public static function remove(string $key): void
    {
        $segments = explode('.', $key);
        $reference = &static::$config;

        foreach ($segments as $index => $segment) {
            if (!isset($reference[$segment]) || !is_array($reference[$segment])) {
                return;
            }

            if ($index === count($segments) - 1) {
                unset($reference[$segment]);
                return;
            }

            $reference = &$reference[$segment];
        }
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        return static::toArray();
    }

    /**
     * @return array
     */
    public static function toArray(): array
    {
        return static::$config;
    }
}