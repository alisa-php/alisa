<?php

namespace Alisa\Support;

class Getter
{
    public function __construct(protected array $data = []) {
        //
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? execute($default);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function all(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return $this->data;
    }
}