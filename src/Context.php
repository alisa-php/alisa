<?php

namespace Alisa;

use Alisa\Directives\AudioPlayer\AudioPlayer;
use Alisa\Exceptions\AlisaException;
use Alisa\Http\Request;
use Alisa\Http\Response;
use Alisa\Scenes\Stage;
use Alisa\Sessions\Application;
use Alisa\Sessions\Session;
use Alisa\Sessions\User;
use Alisa\Support\Render;
use Alisa\Types\Card\AbstractCard;

class Context
{
    public protected(set) Request $request;

    public protected(set) Session $session;

    public protected(set) User $user;

    public protected(set) Application $application;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->session = new Session;
        $this->user = new User;
        $this->application = new Application;
    }

    public function __clone()
    {
        $this->request = clone $this->request;
    }

    public function respond(string $text, ?string $tts = null, array|string $buttons = [], bool $finish = false): void
    {
        $processed = Render::process([
            'text' => $text,
            'tts' => $tts ?? $text,
        ]);

        echo (new Response)
            ->text($processed['text'])
            ->tts($processed['tts'])
            ->buttons($buttons)
            ->finish($finish);
    }

    public function respondWith(AbstractCard|AudioPlayer $type, string $text = '', ?string $tts = null, bool $finish = false): void
    {
        $processed = Render::process([
            'text' => $text,
            'tts' => $tts ?? $text,
        ]);

        $render = new Response;

        if ($type instanceof AbstractCard) {
            $render->withCard($type);
        }

        if ($type instanceof AudioPlayer) {
            $render->withAudioPlayer($type);
        }

        echo $render
            ->text($processed['text'])
            ->tts($processed['tts'])
            ->finish($finish);
    }

    public function enter(string $id): static
    {
        if (!Stage::has($id)) {
            throw new AlisaException("Сцена '{$id}' не существует, создайте ее сначала");
        }

        Session::set('__scene__', $id);

        $scene = Stage::get($id);

        if ($scene->onEnterHandler) {
            execute($scene->onEnterHandler, $this);
        }

        return $this;
    }

    public function leave(?string $id = null): static
    {
        Session::remove('__scene__');

        if ($id !== null) {
            if (!Stage::has($id)) {
                throw new AlisaException("Сцена '{$id}' не существует, создайте ее сначала");
            }

            $scene = Stage::get($id);

            if ($scene->onLeaveHandler) {
                execute($scene->onLeaveHandler, $this);
            }
        }

        return $this;
    }
}