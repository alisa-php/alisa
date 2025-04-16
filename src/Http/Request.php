<?php

namespace Alisa\Http;

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
            return;
        }

        $this->initializeFromInput();
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

    public function has(string $key): bool
    {
        return $this->data->has($key);
    }
}