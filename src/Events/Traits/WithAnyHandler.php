<?php

namespace Alisa\Events\Traits;

use Closure;

trait WithAnyHandler
{
    public function onAny(Closure|array|string $handler, int $priority = 0): static
    {
        $this->on('request.type', $handler, $priority);

        return $this;
    }
}
