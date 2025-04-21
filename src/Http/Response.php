<?php

namespace Alisa\Http;

use Alisa\Types\Directives\AudioPlayer\AudioPlayer;
use Alisa\Sessions\Application;
use Alisa\Sessions\Session;
use Alisa\Sessions\User;
use Alisa\Stores\Buttons;
use Alisa\Types\Button;
use Alisa\Types\Card\AbstractCard;
use JsonException;

class Response
{
    protected array $response = [
        'response' => [
            'text' => null,
            'end_session' => false,
        ],
        'version' => '1.0',
    ];

    public function text(string $text): static
    {
        $this->response['response']['text'] = $text;

        return $this;
    }

    public function tts(string $tts): static
    {
        $this->response['response']['tts'] = $tts;

        return $this;
    }

    /**
     * @param array|string $buttons
     */
    public function buttons(array|string $buttons): static
    {
        $buttons = is_string($buttons) ? Buttons::get($buttons) : $buttons;

        $this->response['response']['buttons'] = $this->resolveButtons((array) $buttons);

        return $this;
    }

    protected function resolveButtons(array $buttons): array
    {
        return array_reduce(
            array_filter($buttons),
            function (array $carry, $button) {
                return array_merge(
                    $carry,
                    match (true) {
                        $button instanceof Button => [$button->toArray()],
                        is_array($button) => $this->resolveButtons($button),
                        is_string($button) => $this->resolveButtons(Buttons::get($button)),
                        default => [],
                    }
                );
            },
            []
        );
    }

    public function withCard(AbstractCard $card): static
    {
        $this->withCustom([
            'response' => [
                'card' => $card->toArray(),
            ],
        ]);

        return $this;
    }

    public function withAudioPlayer(AudioPlayer $player): static
    {
        $this->withCustom([
            'response' => [
                'should_listen' => $player->autoplay,
                'directives' => [
                    'audio_player' => $player->toArray(),
                ],
            ],
        ]);

        return $this;
    }

    public function withCustom(array $data = []): static
    {
        array_replace_recursive($this->response, $data);

        return $this;
    }

    public function finish(bool $value = true): static
    {
        $this->response['response']['end_session'] = $value;

        return $this;
    }

    public function pong(): static
    {
        return $this
            ->text('pong')
            ->tts('pong')
            ->finish();
    }

    /**
     * @throws JsonException
     */
    public function __toString(): string
    {
        $this->addSessionData();

        return json_encode(
            $this->response,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    protected function addSessionData(): void
    {
        if (Session::count() > 0) {
            $this->response['session_state'] = Session::toArray();
        }

        if (Application::count() > 0) {
            $this->response['application_state'] = Application::toArray();
        }

        if (User::count() > 0) {
            $this->response['user_state_update'] = User::toArray();
        }
    }
}