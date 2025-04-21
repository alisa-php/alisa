<?php

namespace Alisa;

class Configuration
{
    /**
     * Значения конфигурации
     *
     * @var array
     */
    protected static array $values = [];

    /**
     * Значения по умолчанию
     *
     * @var array
     */
    protected static array $defaults = [
        'skill_id' => null,
        'oauth_token' => null,
        'fake_request' => null,
        'storage' => [
            'path' => null,
        ],
        'middlewares' => [],
        'components' => [],
        'assets' => [],
        'buttons' => [],
    ];

    public function __construct(array $values = [])
    {
        self::$values = array_replace_recursive(self::$defaults, $values);
    }

    /**
     * @param array $values
     * @return static
     */
    public static function load(array $values): void
    {
        self::$values = array_replace_recursive(self::$defaults, $values);
    }

    /**
     * @return static
     */
    public static function reset(): void
    {
        self::$values = self::$defaults;
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);

        $value = self::$values;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value) && $value[$segment] !== null) {
                $value = $value[$segment];
            } else {
                return execute($default);
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

        $reference = &self::$values;

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

        $value = self::$values;

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
        $reference = &self::$values;

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

        return;
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
        return self::$values;
    }
}