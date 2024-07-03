<?php

namespace Alisa\Yandex\Types;

class Intent
{
    public function __construct(protected array $slots)
    {
        //
    }

    public function slot(string $name, ?array $default = null): mixed
    {
        return $this->slots[$name] ?? $default;
    }
}