<?php

namespace Alisa\Yandex\Types;

use Alisa\Support\Markup;

class Button
{
    protected string $title;

    public function __construct(
        string|array $title,
        protected ?string $action = null,
        protected array $payload = [],
        protected ?string $url = null,
        protected bool $hide = true
    ) {
        $this->title = is_array($title) ? Markup::variant($title) : $title;

        $this->title = Markup::pipe([
            'text' => $this->title, 'tts' => '',
        ], [
            'space', 'plural', 'rand', 'quotes', 'trim',
        ])['text'];
    }

    public function action(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function url(?string $url = null): static
    {
        $this->url = $url;

        return $this;
    }

    public function payload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function hide(bool $hide = true): static
    {
        $this->hide = $hide;

        return $this;
    }

    public function toArray(): array
    {
        $button = [
            'title' => $this->title,
            'hide' => $this->hide,
        ];

        if ($this->url) {
            $button['url'] = $this->url;
        }

        $payload = $this->payload;

        if ($this->action) {
            $payload['__action__'] = $this->action;
        }

        if ($payload) {
            $button['payload'] = $payload;
        }

        return $button;
    }
}