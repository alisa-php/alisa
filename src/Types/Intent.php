<?php

namespace Alisa\Types;

class Intent
{
    public function __construct(protected array $slots)
    {
        //
    }

    public function getSlot(string $name, ?array $default = null): mixed
    {
        return $this->slots[$name] ?? $default;
    }
}