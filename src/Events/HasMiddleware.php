<?php

namespace Alisa\Events;

use Closure;

trait HasMiddleware
{
    protected $middlewares = [];

    public function middleware(Closure|array|string $middleware): static
    {
        $this->middlewares[] = $middleware;
        return $this;
    }
}