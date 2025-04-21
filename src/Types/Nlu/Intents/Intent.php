<?php

namespace Alisa\Types\Nlu\Intents;

class Intent
{
    public function __construct(protected array $data)
    {
        //
    }

    public function slot(string $name, ?array $default = null): ?Slot
    {
        if (isset($this->data['slots'][$name])) {
            return new Slot($this->data['slots'][$name]);
        } else {
            return execute($default);
        }
    }
}