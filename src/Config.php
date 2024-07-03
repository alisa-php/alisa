<?php

namespace Alisa;

use Alisa\Support\Collection;

class Config extends Collection
{
    protected array $items = [
        'skill_id' => null,

        'oauth' => null,

        'storage' => null,

        'payload' => null,

        'buttons' => [],

        'assets' => [],

        'components' => [],

        'middlewares' => [],
    ];

    public function __construct(array $items = [])
    {
        $this->items = array_replace_recursive($this->items, $items);
    }
}