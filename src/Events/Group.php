<?php

namespace Alisa\Events;

use Alisa\Context;
use Closure;

use function Alisa\Support\Helpers\array_flatten;

class Group
{
    /**
     * @var Event[]
     */
    protected array $groups = [];

    protected array $events = [];

    protected array $middlewares = [];

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

    public function on(Closure|array|string $pattern, Closure|array|string $handler, int $priority = 0): Event
    {
        $event = new Event($pattern, $handler);

        $this->events[$priority][] = $event;

        return $event;
    }

    public function onStart(Closure|array|string $handler, int $priority = 0): Event
    {
        $pattern = function (Context $context): bool {
            return
                $context('session.new') === true &&

                // только если комманда пустая,
                // чтобы не пропустить запрос вида: спроси у <навыка> что-нибудь
                in_array($context('request.command'), [null, ''], strict: true);
        };

        return $this->on($pattern, $handler, $priority);
    }

    public function onCommand(array|string $command, Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.command' => $command], $handler, $priority);
    }

    public function onAction(array|string $action, Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.payload.__action__' => $action], $handler, $priority);
    }

    public function onIntent(array|string $id, Closure|array|string $handler, int $priority = 0): Event
    {
        $pattern = function (Context $context) use ($id): bool {
            return (bool) array_intersect((array) $id, array_keys(
                $context('request.nlu.intents', [])
            ));
        };

        return $this->on($pattern, $handler, $priority);
    }

    public function onConfirm(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->onIntent('YANDEX.CONFIRM', $handler, $priority);
    }

    public function onReject(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->onIntent('YANDEX.REJECT', $handler, $priority);
    }

    public function onHelp(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->onIntent('YANDEX.HELP', $handler, $priority);
    }

    public function onRepeat(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->onIntent('YANDEX.REPEAT', $handler, $priority);
    }

    public function onWhatCanYouDo(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->onIntent('YANDEX.WHAT_CAN_YOU_DO', $handler, $priority);
    }

    public function onDangerous(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.markup.dangerous_context' => true], $handler, $priority);
    }

    /**
     * @return Event[]
     */
    protected function events(): array
    {
        return array_flatten($this->events);
    }

    public function middlewares(): array
    {
        return $this->middlewares;
    }

    public function group(Closure $callback, int $priority = 0): Group
    {
        $group = new Group;

        $callback($group);

        $this->groups[$priority][] = $group;

        return $group;
    }

    public function groups(): array
    {
        return array_flatten($this->groups);
    }
}
