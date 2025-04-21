<?php

namespace Alisa\Http;

use Alisa\Exceptions\AlisaException;
use Alisa\Types\Nlu\Entities\Entities;
use Alisa\Types\Nlu\Intents\Intents;
use Alisa\Types\Nlu\Tokens\Tokens;
use Alisa\Support\Collection;

class Request
{
    protected Collection $payload;

    public protected(set) array $raw;

    public function __construct(array $payload = [])
    {
        if (!empty($payload)) {
            $this->initializeFromArray($payload);
        } else {
            $this->initializeFromInput();
        }

        $this->mapNluTokens();
        $this->mapNluEntities();
        $this->mapNluIntents();
    }

    public function __clone()
    {
        $this->payload = clone $this->payload;
    }

    /**
     * @return void
     */
    protected function mapNluTokens(): void
    {
        $tokens = new Tokens($this->get('request.nlu.tokens', []));

        $this->set('request.nlu.tokens', $tokens);
    }

    /**
     * @see https://yandex.ru/dev/dialogs/alice/doc/ru/nlu#concept_nxd_wdj_2kb
     * @return void
     */
    protected function mapNluEntities(): void
    {
        $intents = new Entities($this->get('request.nlu.entities', []));

        $this->set('request.nlu.entities', $intents);
    }

    /**
     * @see https://yandex.ru/dev/dialogs/alice/doc/ru/word-processing
     * @return void
     */
    protected function mapNluIntents(): void
    {
        $intents = new Intents($this->get('request.nlu.intents', []));

        $this->set('request.nlu.intents', $intents);
    }

    protected function initializeFromArray(array $payload): void
    {
        $this->raw = $payload;
        $this->payload = new Collection($payload);
    }

    /**
     * @throws AlisaException
     */
    protected function initializeFromInput(): void
    {
        $input = file_get_contents('php://input');

        if (empty($input)) {
            throw new AlisaException('👋 Все хорошо, но запрос не содержит данных');
        }

        $payload = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AlisaException('Некорректный JSON в запросе: ' . json_last_error_msg());
        }

        $this->raw = $payload;
        $this->payload = new Collection($payload);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->payload->get($key, $default);
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

    /**
     * @see https://yandex.ru/dev/dialogs/alice/doc/ru/health-check
     *
     * @return bool
     */
    public function isPing(): bool
    {
        return
            $this->payload->get('request.original_utterance') === 'ping' &&
            $this->payload->get('request.type') === 'SimpleUtterance';
    }
}