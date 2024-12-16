<?php

namespace Alisa\Entities;

use Alisa\Http\Request;

class Entity
{
    protected string $type;

    public function __construct(public array $entity, protected Request $request)
    {
        $this->type = $entity['type'];
    }

    public function type(): string
    {
        return $this->type;
    }

    public function value(?string $key = null, mixed $default = null): mixed
    {
        // Например, `YANDEX.NUMBER` имеет в поле `value` число, а не массив.
        if (!is_array($this->entity['value'])) {
            return $this->entity['value'];
        }

        return $this->entity['value'][$key] ?? $default;
    }
}