<?php

namespace Alisa\Events;

use Alisa\Context;
use Alisa\Events\Scene;
use Alisa\Support\Concerns\WithAnyHandler;
use Alisa\Support\Concerns\WithFallbackHandler;
use Closure;

use function Alisa\Support\Helpers\call_handler;

class Dispatcher extends Group
{
    use WithFallbackHandler, WithAnyHandler;

    /**
     * @var Scene[]
     */
    protected array $scenes = [];

    protected bool $matched = false;

    public function scene(string $name, Closure $callback): Scene
    {
        $scene = new Scene;

        $callback($scene);

        $this->scenes[$name] = $scene;

        return $scene;
    }

    public function dispatch(Context $context): void
    {
        $globalMiddlewares = $this->middlewares;

        if (($sceneName = $context('state.session.__scene__')) && isset($this->scenes[$sceneName])) {
            $scene = $this->scenes[$sceneName];
            $events = $scene->events();
            $groups = $scene->groups();
            $fallback = $scene->onFallback();
        } else {
            $scene = null;
            $events = $this->events();
            $groups = $this->groups();
            $fallback = $this->onFallback();
        }

        $events = function (Context $context, Closure $next) use ($events) {
            $this->matchEvent($context, $events);

            if ($this->matched) {
                return;
            }

            $next($context);
        };

        $groups = function (Context $context, $next) use ($groups) {
            foreach ($groups as $group) {
                $callbacks = [...$group->middlewares(), function (Context $context) use ($group) {
                    $this->matchEvent($context, $group->events());
                }];

                $this->pipeline($context, $callbacks);

                if ($this->matched) {
                    return;
                }
            }

            $next($context);
        };

        $fallback = function (Context $context) use ($fallback) {
            if (!$this->matched && $fallback) {
                call_handler($fallback, $context);
            }
        };

        $callbacks = [...$globalMiddlewares, $events, $groups, $fallback];

        $this->pipeline($context, $callbacks);
    }

    protected function pipeline(Context $context, array $callbacks): Context
    {
        $next = function () use ($context, $callbacks, &$next) {
            static $index = 0;

            if (count($callbacks) > $index) {
                $callback = $callbacks[$index];
                $index++;

                if (is_callable($callback)) {
                    $callback($context, $next);
                } else {
                    (new ($callback))($context, $next);
                }
            }
        };

        $next();

        return $context;
    }

    protected function matchEvent(Context $context, array $events): void
    {
        foreach ($events as $event) {
            [$matched, $matches] = $event->match($context);

            if (!$matched) {
                continue;
            }

            $callbacks = [...$event->middlewares(), function ($context) use ($event, $matches) {
                if (is_array($matches)) {
                    $event->handle($context, ...$matches);
                } else {
                    $event->handle($context);
                }

                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }

                $this->matched = true;
            }];

            if ($this->matched) {
                return;
            }

            $this->pipeline($context, $callbacks);
        }
    }
}
