<?php

namespace Alisa;

use Alisa\Support\Buttons;
use Alisa\Yandex\Sessions\Application;
use Alisa\Yandex\Sessions\Session;
use Alisa\Yandex\Sessions\User;
use Alisa\Yandex\Types\AudioPlayer\AudioPlayer;
use Alisa\Yandex\Types\Button;
use Alisa\Yandex\Types\Card\Card;

class Render
{
    protected array $response = [
        'response' => [
            'text' => null,
            'end_session' => false,
        ],
        'version' => '1.0',
    ];

    public function withText(string $text): static
    {
        $this->response['response']['text'] = $text;

        return $this;
    }

    public function withTts(string $tts): static
    {
        $this->response['response']['tts'] = $tts;

        return $this;
    }

    public function withButtons(array|string $buttons): static
    {
        if (is_string($buttons)) {
            $buttons = Buttons::get($buttons);
        }

        $this->response['response']['buttons'] = $this->resolveButtons($buttons);

        return $this;
    }

    protected function resolveButtons(array $buttons): array
    {
        $result = [];

        foreach (array_filter($buttons) as $button) {
            if ($button instanceof Button) {
                $result[] = $button->toArray();
            } elseif (is_array($button)) {
                $result = array_merge($result, $this->resolveButtons($button));
            } elseif (is_string($button)) {
                $result = array_merge($result, $this->resolveButtons(Buttons::get($button)));
            }
        }

        return $result;
    }

    public function withCard(Card $card): static
    {
        $this->response['response']['card'] = $card->toArray();

        return $this;
    }

    public function withAudioPlayer(AudioPlayer $player): static
    {
        $this->response['response']['should_listen'] = $player->autoplay();
        $this->response['response']['directives']['audio_player'] = $player->toArray();

        return $this;
    }

    public function finish(bool $value = true): static
    {
        $this->response['response']['end_session'] = $value;

        return $this;
    }

    public function __toString(): string
    {
        if (Session::count() > 0) {
            $this->response['session_state'] = Session::all();
        }

        if (Application::count() > 0) {
            $this->response['application_state'] = Application::all();
        }

        if (User::count() > 0) {
            $this->response['user_state_update'] = User::all();
        }

        return json_encode($this->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}