<?php

namespace Alisa\Events;

use Alisa\Context;
use Alisa\Events\Traits\WithMiddlewares;
use Closure;

use function Alisa\Support\Helpers\pipeline;

class Event
{
    use WithMiddlewares;

    public function __construct(
        protected Closure|string|array $pattern,
        protected Closure|string|array $handler
    ) {
        //
    }

    public function handle(Context $context, ?array $matches = null): void
    {
        if ($matches) {
            call_user_func($this->handler, $context,...$matches);
        } else {
            call_user_func($this->handler, $context);
        }
    }

    public function match(Context $context)
    {
        $hasMatched = false;

        pipeline([
            ...$this->middlewares,
            function (Context $context) use (&$hasMatched) {
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
                                $this->handle($context, is_array($parameters) ? $parameters : []);
                                return $hasMatched = true;
                            } else {
                                continue;
                            }
                        }

                        // [request.command]
                        if (is_numeric($segments) && $context->has($value)) {
                            $this->handle($context);
                            return $hasMatched = true;
                        }

                        // not found
                        if (!$subject = $context->get($segments)) {
                            return $hasMatched = false;
                        }

                        if (is_string($value)) {
                            // точное текстовое совпадение
                            if ($value === $subject) {
                                $this->handle($context);
                                return $hasMatched = true;
                            }

                            // word and {word} {word?}
                            $pattern = preg_replace('~\s{\w+\?}~', '(?: (.*?))?', $value);
                            $pattern = '~^' . preg_replace('/{\w+}/', '(.*?)', $pattern) . '$~iu';

                            if (@preg_match($pattern, $subject, $matches)) {
                                unset($matches[0]);
                                $this->handle($context, $matches);
                                return $hasMatched = true;
                            }
                        }

                        // regex
                        foreach ((array) $value as $pattern) {
                            if (@preg_match($pattern, $subject, $matches)) {
                                unset($matches[0]);
                                $this->handle($context, $matches);
                                return $hasMatched = true;
                            }
                        }
                    }
                }
            }
        ], [$context]);

        return $hasMatched;
    }
}