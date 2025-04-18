<?php

namespace Alisa\Types;

class Intents
{
    public function __construct(protected array $intents = [])
    {
        //
    }

    public function get(string $name, ?Intent $default = null): ?Intent
    {
        return $this->intents[$name] ?? $default;
    }

    public function count(): int
    {
        return count($this->intents);
    }
}