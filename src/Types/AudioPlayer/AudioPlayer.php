<?php

namespace Alisa\Types\AudioPlayer;

class AudioPlayer
{
    protected array $directive = [
        'action' => null,
    ];

    protected bool $autoplay = false;

    public function play(Stream|string $stream, ?Metadata $meta = null, bool $autoplay = false): static
    {
        $this->autoplay = $autoplay;

        $this->directive['action'] = 'Play';

        $this->directive['item'] = [
            'stream' => $stream instanceof Stream ? $stream->toArray() : (new Stream($stream))->toArray(),
            'metadata' => $meta?->toArray(),
        ];

        return $this;
    }

    public function stop(): static
    {
        $this->directive['action'] = 'Stop';

        return $this;
    }

    public function autoplay(): bool
    {
        return $this->autoplay;
    }

    public function toArray(): array
    {
        return $this->directive;
    }
}