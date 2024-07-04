<?php

namespace Alisa;

use Alisa\Exceptions\AlisaException;
use Alisa\Support\Collection;
use Alisa\Support\Markup;
use Alisa\Yandex\Sessions\Session;
use Alisa\Yandex\Types\AudioPlayer\AudioPlayer;
use Alisa\Yandex\Types\Card\Card;
use Alisa\Yandex\Types\Intent;
use Alisa\Yandex\Types\Intents;

class Context
{
    protected Collection $payload;

    protected Intents $intents;

    public function __construct(?array $payload = null)
    {
        $this->payload = $this->capture($payload);
    }

    protected function capture(?array $payload = null): Collection
    {
        if ($payload) {
            return new Collection($payload);
        }

        $input = file_get_contents('php://input');

        if (!$input) {
            throw new AlisaException('Запрос не содержит данных от Яндекс.Диалоги');
        }

        $payload = json_decode($input, true);

        return new Collection($payload);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->payload->get($key, $default);
    }

    public function __invoke(string $key, mixed $default = null): mixed
    {
        return $this->get($key, $default);
    }

    public function set(string $key, mixed $value): static
    {
        $this->payload->set($key, $value);

        return $this;
    }

    public function remove(string $key): static
    {
        $this->payload->remove($key);

        return $this;
    }

    public function has(string $key): bool
    {
        return $this->payload->has($key);
    }

    public function toArray(): array
    {
        return $this->payload->toArray();
    }

    public function isPing(): bool
    {
        return
            $this->get('request.command') === '' &&
            $this->get('request.original_utterance') === 'ping' &&
            $this->get('request.type') === 'SimpleUtterance';
    }

    public function intents(?string $intent = null, ?string $slot = null): Intents|array|null
    {
        if ($intent) {
            return $this->intents()->get($intent)?->slot($slot);
        }

        if (isset($this->intents)) {
            return $this->intents;
        }

        $intents = [];

        foreach ($this->get('request.nlu.intents', []) as $name => $item) {
            $intents[$name] = new Intent($item['slots']);
        }

        $this->intents = new Intents($intents);

        return $this->intents;
    }

    public function entities(): array
    {
        return $this->get('request.nlu.entities', []);
    }

    /**
     * @see https://yandex.ru/dev/dialogs/alice/doc/ru/request#request-desc
     *
     * @return string
     */
    public function type(): string
    {
        return $this->get('request.type');
    }

    /**
     * Уникальный идентификатор сессии, максимум 64 символа.
     *
     * @return int
     */
    public function sessionId(): int
    {
        return $this->get('session.session_id');
    }

    /**
     * Идентификатор пользователя Яндекса, единый для всех приложений и устройств.
     * Этот идентификатор уникален для пары «пользователь — навык»: в разных навыках значение свойства user_id для одного и того же пользователя будет различаться.
     *
     * Этот идентификатор можно использовать как уникальный ключ в БД для пользователя.
     *
     * @return string|null
     */
    public function userId(): ?string
    {
        return $this->get('session.user.user_id');
    }

    /**
     * Идентификатор экземпляра приложения, в котором пользователь общается с Алисой, максимум 64 символа.
     *
     * Даже если пользователь вошел в один и тот же аккаунт в приложение Яндекс для Android и iOS, Яндекс Диалоги присвоят отдельный application_id каждому из этих приложений.
     * Этот идентификатор уникален для пары «приложение — навык»: в разных навыках значение свойства application_id для одного и того же пользователя будет различаться.
     *
     * @return string|null
     */
    public function applicationId(): ?string
    {
        return $this->get('session.application.application_id');
    }

    /**
     * Идентификатор сообщения в рамках сессии, максимум 8 символов.
     *
     * Инкрементируется с каждым следующим запросом.
     *
     * @return int
     */
    public function messageId(): int
    {
        return $this->get('session.message_id');
    }

    public function skillId(): string
    {
        return $this->get('session.skill_id');
    }

    public function command(): ?string
    {
        return $this->get('request.command');
    }

    public function originalUtterance(): ?string
    {
        return $this->get('request.original_utterance');
    }

    public function payload(): Collection
    {
        return new Collection($this->get('request.payload', []));
    }

    public function __toString(): string
    {
        return json_encode(
            $this->payload->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    public function enter(string $scene): static
    {
        Session::set('__scene__', $scene);

        return $this;
    }

    public function leave(string $scene)
    {
        Session::set('__scene__', null);

        return $this;
    }

    public function reply(string $text, ?string $tts = null, array|string $buttons = [], bool $finish = false): void
    {
        $processed = Markup::process([
            'text' => $text,
            'tts' => $tts ?? $text,
        ]);

        echo (new Render)
            ->withText($processed['text'])
            ->withTts($processed['tts'])
            ->withButtons($buttons)
            ->finish($finish);
    }

    public function finish(string $text, ?string $tts = null, array|string $buttons = []): void
    {
        $this->reply($text, $tts, $buttons, finish: true);
    }

    public function replyWith(Card|AudioPlayer $type, string $text = '', ?string $tts = null, bool $finish = false): void
    {
        $processed = Markup::process([
            'text' => $text,
            'tts' => $tts ?? $text,
        ]);

        $render = new Render;

        if ($type instanceof Card) {
            $render->withCard($type);
        }

        if ($type instanceof AudioPlayer) {
            $render->withAudioPlayer($type);
        }

        echo $render
            ->withText($processed['text'])
            ->withTts($processed['tts'])
            ->finish($finish);
    }
}