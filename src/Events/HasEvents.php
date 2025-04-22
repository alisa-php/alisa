<?php

namespace Alisa\Events;

use Alisa\Context;
use Closure;

trait HasEvents
{
    protected $handlers = [
        'listeners' => [],
        'fallback' => null,
        'error' => null,
    ];

    public function on(
        Closure|string|array $pattern,
        Closure|array|string $handler,
        int $priority = 0
    ): Event {
        $event = new Event($pattern, $handler);
        $this->handlers['listeners'][$priority][] = $event;
        return $event;
    }

    public function onFallback(Closure|array|string|null $handler = null): static
    {
        $this->handlers['fallback'] = $handler;
        return $this;
    }

    public function onError(Closure|array|string $handler): static
    {
        $this->handlers['error'] = $handler;
        return $this;
    }

    public function dispatch(Context $context): void
    {
        $processEvents = $this->createEventProcessor();
        $fallbackHandler = $this->createFallbackHandler();

        $callbacks = [$processEvents, $fallbackHandler];

        if (property_exists($this, 'middlewares')) {
            array_unshift($callbacks, ...$this->middlewares);
        }

        try {
            pipeline($callbacks, $context);
        } catch (\Throwable $th) {
            if ($this->handlers['error'] !== null) {
                execute($this->handlers['error'], $context, $th);
            } else {
                throw $th;
            }
        }
    }

    protected function createEventProcessor(): Closure
    {
        return function (Context $context, Closure $next) {
            $matched = $this->findMatchingEvent($context);

            if (!$matched) {
                $next($context);
            }
        };
    }

    protected function findMatchingEvent(Context $context): bool
    {
        /** @var Event[] */
        $events = $this->getSortedEvents();

        foreach ($events as $event) {
            if ($event->match($context)) {
                return true;
            }
        }

        return false;
    }

    protected function getSortedEvents(): array
    {
        $eventsByPriority = $this->handlers['listeners'];
        ksort($eventsByPriority);

        return array_merge(...array_values($eventsByPriority));
    }

    protected function createFallbackHandler(): Closure
    {
        return function (Context $context) {
            if ($this->handlers['fallback'] !== null) {
                call_user_func($this->handlers['fallback'], $context);
            }
        };
    }
}