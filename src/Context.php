<?php

namespace Alisa;

use Alisa\Entities\Entity;
use Alisa\Http\Request;
use Alisa\Http\Response;
use Alisa\Scenes\Stage;
use Alisa\Support\Collection;
use Alisa\Support\Render;
use Alisa\Types\AudioPlayer\AudioPlayer;
use Alisa\Types\Card\AbstractCard;
use Alisa\Types\Intent;
use Alisa\Types\Intents;
use Alisa\Types\Request\Meta;

class Context extends Request
{
    protected Intents $intents;

    public function __construct(protected Request $request)
    {
        parent::__construct($request->toArray());

        $this->processIntents();
    }

    protected function processIntents(): void
    {
        $intents = [];

        foreach ($this->get('request.nlu.intents', []) as $name => $item) {
            $intents[$name] = new Intent($item['slots']);
        }

        $this->intents = new Intents($intents);
    }

    public function reply(string $text, ?string $tts = null, array|string $buttons = [], bool $finish = false): void
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

    public function bye(string $text, ?string $tts = null, array|string $buttons = []): void
    {
        $this->reply($text, $tts, $buttons, finish: true);
    }

    public function replyWith(AbstractCard|AudioPlayer $type, string $text = '', ?string $tts = null, bool $finish = false): void
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

    /**
     * Идентификатор вызываемого навыка, присвоенный при создании.
     *
     * @return strong|null
     */
    public function getSkillId(): ?string
    {
        return $this->get('session.skill_id');
    }

    public function getMessageId(): int
    {
        return $this->get('session.message_id');
    }

    /**
     * Получить объект Meta.
     *
     * @return Meta
     */
    public function getMeta(): Meta
    {
        return new Meta($this->get('meta', []));
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        return $this->get('meta.' . $key, $default);
    }

    public function getIntent(string $intent): Intent|null
    {
        return $this->intents->get($intent);
    }

    public function getIntents(): Intents
    {
        return $this->intents;
    }

    public function getTokens(): array
    {
        return $this->get('request.nlu.tokens', []);
    }

    /**
     * Получить NLU сущности.
     *
     * @return Entity[]
     */
    public function getEntities(): array
    {
        return $this->get('request.nlu.entities', []);
    }

    /**
     * Тип ввода.
     *
     * Возможные значения:
     * - `SimpleUtterance` — голосовой ввод.
     * - `ButtonPressed` — нажатие кнопки.
     * - `AudioPlayer.PlaybackStarted` — событие начала воспроизведения аудиоплеером на умных колонках.
     * - `AudioPlayer.PlaybackFinished` — событие завершения воспроизведения.
     * - `AudioPlayer.PlaybackNearlyFinished` — событие о скором завершении воспроизведения текущего трека.
     * - `AudioPlayer.PlaybackStopped` — остановка воспроизведения.
     * - `AudioPlayer.PlaybackFailed` — ошибка воспроизведения.
     * - `Purchase.Confirmation` — запрос на подтверждение оплаты в навыке.
     * - `Show.Pull` — запрос на чтение данных для шоу.
     *
     * @see https://yandex.ru/dev/dialogs/alice/doc/ru/request#request-desc
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->get('request.type');
    }

    public function isType(string $type): bool
    {
        return $this->getType() === $type;
    }

    public function getCommand(): ?string
    {
        return $this->get('request.command');
    }

    public function getOriginalUtterance(): ?string
    {
        return $this->get('request.original_utterance');
    }

    public function enter(string $sceneId): static
    {
        Stage::enter($sceneId);

        return $this;
    }

    public function leave(string $sceneId): static
    {
        Stage::leave($sceneId);

        return $this;
    }

    public function getRequest(): Collection
    {
        return new Collection($this->get('request', []));
    }

    public function request(string $key, mixed $default = null): mixed
    {
        return $this->get('request.' . $key, $default);
    }

    public function getPayload(): Collection
    {
        return new Collection($this->get('request.payload', []));
    }

    public function payload(string $key, mixed $default = null): mixed
    {
        return $this->get('request.payload.' . $key, $default);
    }
}