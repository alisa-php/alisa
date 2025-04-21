<?php

namespace Alisa\Events;

use Alisa\Context;
use Alisa\Sessions\Session;
use Closure;

trait HasDialogEvents
{
    public function onStart(Closure|array|string $handler, int $priority = 0): Event
    {
        $pattern = function (Context $context): bool {
            return
                Session::isNew() &&

                // только если команда пустая,
                // чтобы не пропустить запрос вида: спроси у <навыка> что-нибудь
                in_array($context->request->get('request.command'), [null, ''], strict: true);
        };

        return $this->listen($pattern, $handler, $priority);
    }

    public function onCommand(array|string $command, Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen(['request.command' => $command], $handler, $priority);
    }

    public function onAction(array|string $action, Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen(['request.payload.__action__' => $action], $handler, $priority);
    }

    public function onAny(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen('request.type', $handler, $priority);
    }

    public function onIntent(array|string $id, Closure|array|string $handler, int $priority = 0): Event
    {
        $pattern = function (Context $context) use ($id): bool {
            return (bool) array_intersect((array) $id, array_keys(
                $context->request->get('request.nlu.intents')->toArray()
            ));
        };

        return $this->listen($pattern, $handler, $priority);
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
        return $this->listen(['request.markup.dangerous_context' => true], $handler, $priority);
    }

    public function onPurchaseConfirmation(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen(['request.type' => 'Purchase.Confirmation'], $handler, $priority);
    }

    public function onShowPull(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen(['request.type' => 'Show.Pull'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackStarted(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen(['request.type' => 'AudioPlayer.PlaybackStarted'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackFinished(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen(['request.type' => 'AudioPlayer.PlaybackFinished'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackNearlyFinished(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen(['request.type' => 'AudioPlayer.PlaybackNearlyFinished'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackStopped(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen(['request.type' => 'AudioPlayer.PlaybackStopped'], $handler, $priority);
    }

    public function onAudioPlayerPlaybackFailed(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->listen(['request.type' => 'AudioPlayer.PlaybackFailed'], $handler, $priority);
    }
}