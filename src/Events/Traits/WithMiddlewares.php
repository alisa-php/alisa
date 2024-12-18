<?php

namespace Alisa\Events\Traits;

use Alisa\Stores\Middlewares;
use Closure;

trait WithMiddlewares
{
    protected array $middlewares = [];

    public function middleware(Closure|array|string $middlewares): static
    {
        if (is_array($middlewares)) {
            foreach ($middlewares as $middleware) {
                if ($middleware instanceof Closure) {
                    $this->middlewares[] = $middleware;
                } else if (is_string($middleware)) {
                    $this->middlewares[] = Middlewares::get($middleware, $middleware);
                }
            }
        } else if ($middlewares instanceof Closure) {
            $this->middlewares[] = $middlewares;
        } else if (is_string($middlewares)) {
            $this->middlewares[] = Middlewares::get($middlewares);
        }

        return $this;
    }
}