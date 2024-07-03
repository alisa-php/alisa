<?php

namespace Alisa\Events;

use Alisa\Context;
use Closure;

use function Alisa\Support\Helpers\call_handler;

class Event
{
    protected array $middlewares = [];

    public function __construct(
        protected Closure|array|string $pattern,
        protected Closure|array|string $handler
    ) {
        //
    }

    public function match(Context $context): array
    {
        if (is_string($this->pattern)) {
            $this->pattern = [$this->pattern];
        }

        if ($this->pattern instanceof Closure) {
            $this->pattern = [$this->pattern];
        }

        foreach ($this->pattern as $segments => $values) {
            foreach ((array) $values as $value) {
                // closure
                if ($value instanceof Closure) {
                    if ($parameters = call_user_func($value, $context)) {
                        return [true, is_array($parameters) ? $parameters : null];
                    } else {
                        continue;
                    }
                }

                // [requesst.command]
                if (is_numeric($segments) && $context->has($value)) {
                    return [true, null];
                }

                // not found
                if (!$subject = $context->get($segments)) {
                    return [false, null];
                }

                if (is_string($value)) {
                    // точное текстовое совпадение
                    if ($value === $subject) {
                        return [true, null];
                    }

                    // word and {word} {word?}
                    $pattern = preg_replace('~\s{\w+\?}~', '(?: (.*?))?', $value);
                    $pattern = '~^' . preg_replace('/{\w+}/', '(.*?)', $pattern) . '$~iu';

                    if (@preg_match($pattern, $subject, $matches)) {
                        unset($matches[0]);
                        return [true, $matches];
                    }
                }

                // regex
                foreach ((array) $value as $pattern) {
                    if (@preg_match($pattern, $subject, $matches)) {
                        unset($matches[0]);
                        return [true, $matches];
                    }
                }
            }
        }

        return [false, null];
    }

    public function handle(Context $context, ...$matches): void
    {
        call_handler($this->handler, $context, ...$matches);
    }

    public function middleware(Closure|array|string $callback): static
    {
        if (!is_array($callback)) {
            $callback = [$callback];
        }

        foreach ($callback as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    public function middlewares(): array
    {
        return $this->middlewares;
    }
}
