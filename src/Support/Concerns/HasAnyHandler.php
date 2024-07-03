<?php

namespace Alisa\Support\Concerns;

use Closure;

trait HasAnyHandler
{
    public function onAny(Closure|array|string $handler, int $priority = 0): static
    {
        $this->on('request.type', $handler, $priority);

        return $this;
    }
}
