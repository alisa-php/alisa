<?php

namespace Alisa\Http;

use Alisa\Entities\DatetimeEntity;
use Alisa\Entities\Entity;
use Alisa\Entities\FioEntity;
use Alisa\Entities\GeoEntity;
use Alisa\Entities\NumberEntity;
use Alisa\Exceptions\AlisaException;
use Alisa\Support\Collection;

class Request
{
    protected Collection $data;

    public protected(set) array $raw;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->initializeFromArray($data);
        } else {
            $this->initializeFromInput();
        }

        $this->mapEntities();
    }

    public function __clone()
    {
        $this->data = clone $this->data;
    }

    /**
     * @see https://yandex.ru/dev/dialogs/alice/doc/naming-entities.html
     * @return void
     */
    protected function mapEntities(): void
    {
        foreach ($this->get('request.nlu.entities', []) as $key => $entity) {
            $this->set('request.nlu.entities.' . $key, match ($entity['type']) {
                'YANDEX.FIO' => new FioEntity($entity),
                'YANDEX.GEO' => new GeoEntity($entity),
                'YANDEX.NUMBER' => new NumberEntity($entity),
                'YANDEX.DATETIME' => new DatetimeEntity($entity),
                default => new Entity($entity),
            });
        }
    }

    protected function initializeFromArray(array $data): void
    {
        $this->raw = $data;
        $this->data = new Collection($data);
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

        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AlisaException('Некорректный JSON в запросе: ' . json_last_error_msg());
        }

        $this->raw = $data;
        $this->data = new Collection($data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data->get($key, $default);
    }

    public function set(string $key, mixed $value): static
    {
        $this->data->set($key, $value);

        return $this;
    }

    public function remove(string $key): static
    {
        $this->data->remove($key);

        return $this;
    }

    public function has(string $key): bool
    {
        return $this->data->has($key);
    }

    /**
     * @see https://yandex.ru/dev/dialogs/alice/doc/ru/health-check
     *
     * @return bool
     */
    public function isPing(): bool
    {
        return
            $this->data->get('request.original_utterance') === 'ping' &&
            $this->data->get('request.type') === 'SimpleUtterance';
    }
}