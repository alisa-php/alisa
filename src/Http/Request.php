<?php

namespace Alisa\Http;

use Alisa\Exceptions\AlisaException;
use Alisa\Support\Collection;

class Request extends Collection
{
    protected array $originalItems = [];

    public function __construct(?array $items = null)
    {
        if (is_array($items) && $items !== []) {
            $this->originalItems = $items;
            $this->items = $this->originalItems;
            return;
        }

        $input = file_get_contents('php://input');

        if (!$input) {
            throw new AlisaException(
                'Всё хорошо, но запрос не содержит данных от Диалога.'
            );
        }

        $this->originalItems = json_decode($input, true);
        $this->items = $this->originalItems;
    }

    /**
     * @see https://yandex.ru/dev/dialogs/alice/doc/ru/health-check
     *
     * @return bool
     */
    public function isPing(): bool
    {
        return
            // $this->get('request.command') === 'ping' &&
            $this->get('request.original_utterance') === 'ping' &&
            $this->get('request.type') === 'SimpleUtterance';
    }
}