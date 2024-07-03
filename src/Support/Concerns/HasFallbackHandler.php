<?php

namespace Alisa\Support\Concerns;

use Closure;

trait HasFallbackHandler
{
    protected Closure|array|string|null $fallbackHandler = null;

    public function onFallback(Closure|array|string|null $handler = null): Closure|array|string|static|null
    {
        if (!$handler) {
            return $this->fallbackHandler;
        }

        $this->fallbackHandler = $handler;

        return $this;
    }
}
