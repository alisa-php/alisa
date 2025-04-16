<?php

namespace Alisa;

class Configuration
{
    /**
     * Значения конфигурации
     *
     * @var array
     */
    protected array $values = [];

    /**
     * Значения по умолчанию
     *
     * @var array
     */
    protected array $defaults = [
        'skill_id' => null,

        'oauth_token' => null,

        'storage' => [
            'path' => null,
        ],

        'middlewares' => [],

        'components' => [],

        'assets' => [],

        'buttons' => [],
    ];

    /**
     * @param array $values
     * @return static
     */
    public function __construct(array $values = [])
    {
        $this->load($values);
    }

    /**
     * @param array $values
     * @return static
     */
    public function load(array $values): static
    {
        $this->values = array_replace_recursive($this->defaults, $values);

        return $this;
    }

    /**
     * @return static
     */
    public function reset(): static
    {
        $this->values = $this->defaults;

        return $this;
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);

        $value = $this->values;

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
    public function set(string $key, mixed $value): static
    {
        $segments = explode('.', $key);

        $reference = &$this->values;

        foreach ($segments as $segment) {
            if (!isset($reference[$segment]) || !is_array($reference[$segment])) {
                $reference[$segment] = [];
            }

            $reference = &$reference[$segment];
        }

        $reference = $value;

        return $this;
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $segments = explode('.', $key);

        $value = $this->values;

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
    public function remove(string $key): static
    {
        $segments = explode('.', $key);
        $reference = &$this->values;

        foreach ($segments as $index => $segment) {
            if (!isset($reference[$segment]) || !is_array($reference[$segment])) {
                return $this;
            }

            if ($index === count($segments) - 1) {
                unset($reference[$segment]);
                return $this;
            }

            $reference = &$reference[$segment];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return static::toArray();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->values;
    }
}