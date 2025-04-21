<?php

namespace Alisa\Events;

use Alisa\Context;
use Closure;

class Event
{
    use HasMiddleware;

    public function __construct(
        protected Closure|string|array $pattern,
        protected Closure|string|array $handler
    ) {
        //
    }

    public function __invoke(Context $context, array $arguments = []): void
    {
        execute($this->handler, $context, ...$arguments);
    }

    public function match(Context $context): bool
    {
        $hasMatched = false;

        $matchProcessor = function (Context $context) use (&$hasMatched) {
            $this->normalizePattern();
            $hasMatched = $this->processPatternMatch($context);
        };

        pipeline([...$this->middlewares, $matchProcessor], clone $context);

        return $hasMatched;
    }

    protected function normalizePattern(): void
    {
        if (is_string($this->pattern)) {
            $this->pattern = [$this->pattern];
        }

        if ($this->pattern instanceof Closure) {
            $this->pattern = [$this->pattern];
        }
    }

    protected function processPatternMatch(Context $context): bool
    {
        foreach ($this->pattern as $segments => $values) {
            foreach ((array) $values as $value) {
                if ($this->matchClosure($context, $value)) {
                    return true;
                }

                if ($this->matchDirectContextValue($context, $segments, $value)) {
                    return true;
                }

                if ($this->matchStringPattern($context, $segments, $value)) {
                    return true;
                }

                if ($this->matchRegexPattern($context, $segments, $value)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function matchClosure(Context $context, mixed $closure): bool
    {
        if (!($closure instanceof Closure)) {
            return false;
        }

        if ($closure($context)) {
            $this($context);
        }

        return true;
    }

    protected function matchDirectContextValue(Context $context, mixed $segments, mixed $value): bool
    {
        if (!is_numeric($segments) || !$context->request->has($value)) {
            return false;
        }

        $this($context);
        return true;
    }

    protected function matchStringPattern(Context $context, mixed $segments, mixed $value): bool
    {
        if (!is_string($value) || !$subject = $context->request->get($segments)) {
            return false;
        }

        if ($value === $subject) {
            $this($context);
            return true;
        }

        $pattern = $this->createPatternFromString($value);

        if (@preg_match($pattern, $subject, $matches)) {
            $this($context, array_slice($matches, 1));
            return true;
        }

        return false;
    }

    protected function createPatternFromString(string $value): string
    {
        $pattern = preg_replace('~\s{\w+\?}~', '(?: (.*?))?', $value);
        return '~^' . preg_replace('/{\w+}/', '(.*?)', $pattern) . '$~u';
    }

    protected function matchRegexPattern(Context $context, mixed $segments, mixed $value): bool
    {
        if (!is_string($segments) || !$subject = $context->request->get($segments)) {
            return false;
        }

        foreach ((array) $value as $pattern) {
            if (!is_string($pattern)) {
                continue;
            }

            if (@preg_match($pattern, $subject, $matches)) {
                $this($context, array_slice($matches, 1));
                return true;
            }
        }

        return false;
    }
}