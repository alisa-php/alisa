<?php

namespace Alisa\Support;

use ArrayAccess;
use Countable;

class Collection implements ArrayAccess, Countable
{
    protected array $items = [];

    /**
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);

        $value = $this->items;

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

        $reference = &$this->items;

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

        $value = $this->items;

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
        $reference = &$this->items;

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
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * @return string
     */
    public function toJson(int $flags = 0, int $depth = 512): string
    {
        return json_encode($this->items, $flags, $depth);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->items[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @return string
     */
    public function __serialize()
    {
        return serialize($this->items);
    }

    /**
     * @param string $data
     * @return void
     */
    public function __unserialize($data)
    {
        $this->items = unserialize($data);
    }
}