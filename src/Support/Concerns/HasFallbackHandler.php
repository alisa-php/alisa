<?php

namespace Alisa\Support\Concerns;

use Closure;

trait HasFallbackHandler
{
    protected ?Closure $fallbackHandler = null;

    public function onFallback(?Closure $handler = null): Closure|static|null
    {
        if (!$handler) {
            return $this->fallbackHandler;
        }

        $this->fallbackHandler = $handler;

        return $this;
    }
}
