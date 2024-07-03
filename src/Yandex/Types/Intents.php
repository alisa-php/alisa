<?php

namespace Alisa\Yandex\Types;

class Intents
{
    public function __construct(protected array $intents)
    {
        //
    }

    public function get(string $name, ?Intent $default = null): ?Intent
    {
        return $this->intents[$name] ?? $default;
    }
}