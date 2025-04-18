<?php

namespace Alisa;

use Alisa\Directives\AudioPlayer\AudioPlayer;
use Alisa\Http\Request;
use Alisa\Http\Response;
use Alisa\Support\Render;
use Alisa\Types\Card\AbstractCard;

class Context
{
    public protected(set) Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
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
}