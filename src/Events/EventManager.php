<?php

namespace Alisa\Events;

use Alisa\Context;
use Alisa\Events\Traits\WithMiddlewares;
use Alisa\Http\Request;
use Closure;

use function Alisa\Support\Helpers\array_flatten;
use function Alisa\Support\Helpers\pipeline;

class EventManager
{
    use WithMiddlewares;

    protected array $events = [];

    protected ?Closure $fallbackHandler = null;

    public function on(
        Closure|string|array $pattern,
        Closure|array|string $handler,
        int $priority = 0
    ): Event {
        $event = new Event($pattern, $handler);

        $this->events[$priority][] = $event;

        return $event;
    }

    public function onStart(Closure|array|string $handler, int $priority = 0): Event
    {
        $pattern = function (Context $context): bool {
            return
                $context('session.new') === true &&

                // только если команда пустая,
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

    public function onPurchaseConfirmation(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.type' => 'Purchase.Confirmation'], $handler, $priority);
    }

    public function onShowPull(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.type' => 'Show.Pull'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackStarted(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.type' => 'AudioPlayer.PlaybackStarted'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackFinished(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.type' => 'AudioPlayer.PlaybackFinished'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackNearlyFinished(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.type' => 'AudioPlayer.PlaybackNearlyFinished'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackStopped(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.type' => 'AudioPlayer.PlaybackStopped'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackFailed(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->on(['request.type' => 'AudioPlayer.PlaybackFailed'], $handler, $priority);
    }

    public function onFallback(?Closure $handler = null): static
    {
        $this->fallbackHandler = $handler;

        return $this;
    }

    public function dispatch(Request $request): void
    {
        $events = function (Context $context, Closure $next) {
            /** @var Event[] */
            $events = array_flatten($this->events);

            $hasMatched = false;

            foreach ($events as $event) {
                if ($hasMatched = $event->match($context)) {
                    break;
                }
            }

            if (!$hasMatched) {
                $next($context);
            }
        };

        $default = function (Context $context) {
            if ($this->fallbackHandler) {
                call_user_func($this->fallbackHandler, $context);
            }
        };

        pipeline([
            ...$this->middlewares,
            $events,
            $default,
        ], [new Context($request)]);
    }
}