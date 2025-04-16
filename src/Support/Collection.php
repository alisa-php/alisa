<?php

namespace Alisa\Support;

use ArrayAccess;
use Countable;
use JsonSerializable;

class Collection implements ArrayAccess, Countable, JsonSerializable
{
    /**
     * Хранилище элементов коллекции.
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Создает новый экземпляр коллекции.
     *
     * @param array $items     Начальные элементы коллекции
     * @param string $delimiter Разделитель для доступа к вложенным элементам
     * @param string $wildcard Символ для обозначения маски при поиске
     */
    public function __construct(array $items = [], protected string $delimiter = '.', protected string $wildcard = '*')
    {
        $this->items = $items;
    }

    /**
     * Разбивает ключ на сегменты с учетом разделителя.
     *
     * @param string $key Составной ключ
     * @return array Массив сегментов
     */
    protected function parseKey(string $key): array
    {
        if ($this->delimiter === '') {
            return [$key];
        }

        return explode($this->delimiter, $key);
    }

    /**
     * Проверяет, содержит ли ключ маску.
     *
     * @param string $key Проверяемый ключ
     * @return bool
     */
    protected function hasWildcard(string $key): bool
    {
        return $this->wildcard !== '' && str_contains($key, $this->wildcard);
    }

    /**
     * Находит все ключи, соответствующие шаблону с маской.
     *
     * @param array $array Массив для поиска
     * @param string $pattern Шаблон с маской
     * @return array Найденные ключи
     */
    protected function matchWildcard(array $array, string $pattern): array
    {
        $matches = [];
        $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';

        foreach (array_keys($array) as $key) {
            if (preg_match($regex, (string)$key)) {
                $matches[] = $key;
            }
        }

        return $matches;
    }

    /**
     * Получает значение по указанному ключу с поддержкой вложенности и масок.
     *
     * @param string $key Ключ для поиска
     * @param mixed $default Значение по умолчанию, если ключ не найден
     * @return mixed Найденное значение или значение по умолчанию
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $this->items;
        }

        $segments = $this->parseKey($key);
        $results = [];
        $hasResults = false;

        // Рекурсивная функция для обработки вложенных элементов и масок
        $getValueFromPath = function($data, $pathSegments, $currentIndex = 0) use (&$getValueFromPath, &$results, &$hasResults, $default) {
            // Если достигли конца пути, возвращаем текущее значение
            if ($currentIndex >= count($pathSegments)) {
                $hasResults = true;
                $results[] = $data;
                return;
            }

            $segment = $pathSegments[$currentIndex];

            // Если сегмент содержит маску
            if ($this->hasWildcard($segment)) {
                if (!is_array($data)) {
                    return;
                }

                // Находим все соответствующие ключи
                $matchedKeys = $this->matchWildcard($data, $segment);

                foreach ($matchedKeys as $matchedKey) {
                    $getValueFromPath($data[$matchedKey], $pathSegments, $currentIndex + 1);
                }

                return;
            }

            // Обычный случай - прямой доступ по ключу
            if (is_array($data) && array_key_exists($segment, $data)) {
                $getValueFromPath($data[$segment], $pathSegments, $currentIndex + 1);
            }
        };

        $getValueFromPath($this->items, $segments);

        // Если нашли один результат, возвращаем его напрямую
        if (count($results) === 1) {
            return $results[0];
        }
        // Если нашли несколько результатов, возвращаем массив
        elseif ($hasResults) {
            return $results;
        }

        return $default;
    }

    /**
     * Устанавливает значение по указанному ключу с поддержкой вложенности и масок.
     *
     * @param string $key Ключ для установки
     * @param mixed $value Устанавливаемое значение
     * @return static Текущий экземпляр коллекции для цепочки вызовов
     */
    public function set(string $key, mixed $value): static
    {
        if ($key === '') {
            $this->items = (array)$value;
            return $this;
        }

        $segments = $this->parseKey($key);

        // Рекурсивная функция для установки значения с поддержкой масок
        $setValue = function (&$data, $pathSegments, $currentIndex, $finalValue) use (&$setValue) {
            $segment = $pathSegments[$currentIndex];

            // Если это последний сегмент в пути
            if ($currentIndex === count($pathSegments) - 1) {
                // Если сегмент содержит маску
                if ($this->hasWildcard($segment)) {
                    $matchedKeys = $this->matchWildcard($data, $segment);
                    foreach ($matchedKeys as $matchedKey) {
                        $data[$matchedKey] = $finalValue;
                    }
                } else {
                    $data[$segment] = $finalValue;
                }
                return;
            }

            // Если сегмент содержит маску
            if ($this->hasWildcard($segment)) {
                $matchedKeys = $this->matchWildcard($data, $segment);
                foreach ($matchedKeys as $matchedKey) {
                    if (!isset($data[$matchedKey]) || !is_array($data[$matchedKey])) {
                        $data[$matchedKey] = [];
                    }
                    $setValue($data[$matchedKey], $pathSegments, $currentIndex + 1, $finalValue);
                }
            } else {
                if (!isset($data[$segment]) || !is_array($data[$segment])) {
                    $data[$segment] = [];
                }
                $setValue($data[$segment], $pathSegments, $currentIndex + 1, $finalValue);
            }
        };

        $setValue($this->items, $segments, 0, $value);

        return $this;
    }

    /**
     * Проверяет наличие значения по указанному ключу с поддержкой вложенности и масок.
     *
     * @param string $key Ключ для проверки
     * @return bool Результат проверки
     */
    public function has(string $key): bool
    {
        if ($key === '') {
            return !empty($this->items);
        }

        $segments = $this->parseKey($key);
        $exists = false;

        // Рекурсивная функция для проверки существования пути
        $checkPath = function($data, $pathSegments, $currentIndex = 0) use (&$checkPath, &$exists) {
            // Если достигли конца пути, значит путь существует
            if ($currentIndex >= count($pathSegments)) {
                $exists = true;
                return;
            }

            $segment = $pathSegments[$currentIndex];

            // Если сегмент содержит маску
            if ($this->hasWildcard($segment)) {
                if (!is_array($data)) {
                    return;
                }

                // Находим все соответствующие ключи
                $matchedKeys = $this->matchWildcard($data, $segment);

                if (empty($matchedKeys)) {
                    return;
                }

                foreach ($matchedKeys as $matchedKey) {
                    $checkPath($data[$matchedKey], $pathSegments, $currentIndex + 1);
                    // Если нашли хотя бы один подходящий путь, можно остановиться
                    if ($exists) {
                        return;
                    }
                }

                return;
            }

            // Обычный случай - прямой доступ по ключу
            if (is_array($data) && array_key_exists($segment, $data)) {
                $checkPath($data[$segment], $pathSegments, $currentIndex + 1);
            }
        };

        $checkPath($this->items, $segments);

        return $exists;
    }

    /**
     * Удаляет значение по указанному ключу с поддержкой вложенности и масок.
     *
     * @param string $key Ключ для удаления
     * @return static Текущий экземпляр коллекции для цепочки вызовов
     */
    public function remove(string $key): static
    {
        if ($key === '') {
            $this->items = [];
            return $this;
        }

        $segments = $this->parseKey($key);

        // Рекурсивная функция для удаления значения с поддержкой масок
        $removeValue = function (&$data, $pathSegments, $currentIndex = 0) use (&$removeValue) {
            // Если нет больше сегментов или данные не массив, завершаем
            if ($currentIndex >= count($pathSegments) || !is_array($data)) {
                return;
            }

            $segment = $pathSegments[$currentIndex];
            $isLastSegment = ($currentIndex === count($pathSegments) - 1);

            // Если сегмент содержит маску
            if ($this->hasWildcard($segment)) {
                $matchedKeys = $this->matchWildcard($data, $segment);

                foreach ($matchedKeys as $matchedKey) {
                    if ($isLastSegment) {
                        unset($data[$matchedKey]);
                    } else {
                        $removeValue($data[$matchedKey], $pathSegments, $currentIndex + 1);
                    }
                }
            } else {
                if (!array_key_exists($segment, $data)) {
                    return;
                }

                if ($isLastSegment) {
                    unset($data[$segment]);
                } else {
                    $removeValue($data[$segment], $pathSegments, $currentIndex + 1);
                }
            }
        };

        $removeValue($this->items, $segments);

        return $this;
    }

    /**
     * Возвращает количество элементов в коллекции.
     *
     * @return int Количество элементов
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Возвращает все элементы коллекции как массив.
     *
     * @return array Массив элементов
     */
    public function all(): array
    {
        return $this->toArray();
    }

    /**
     * Преобразует коллекцию в массив.
     *
     * @return array Массив элементов
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Преобразует коллекцию в строку JSON.
     *
     * @param int $flags Флаги для json_encode
     * @param int $depth Максимальная глубина рекурсии
     * @return string JSON-представление коллекции
     */
    public function toJson(int $flags = 0, int $depth = 512): string
    {
        return json_encode($this->items, $flags, $depth);
    }

    /**
     * Возвращает данные для сериализации в JSON.
     *
     * @return array Данные для сериализации
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }

    /**
     * Преобразует коллекцию в строку.
     *
     * @return string JSON-представление коллекции
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Проверяет существование элемента по смещению.
     *
     * @param mixed $offset Смещение
     * @return bool Результат проверки
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Получает элемент по смещению.
     *
     * @param mixed $offset Смещение
     * @return mixed Значение элемента
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * Устанавливает элемент по смещению.
     *
     * @param mixed $offset Смещение
     * @param mixed $value Значение
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Удаляет элемент по смещению.
     *
     * @param mixed $offset Смещение
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Подготавливает данные для сериализации.
     *
     * @return array Данные для сериализации
     */
    public function __serialize(): array
    {
        return [
            'items' => $this->items,
            'delimiter' => $this->delimiter,
            'wildcard' => $this->wildcard,
        ];
    }

    /**
     * Восстанавливает объект после десериализации.
     *
     * @param array $data Десериализованные данные
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->items = $data['items'];
        $this->delimiter = $data['delimiter'] ?? '.';
        $this->wildcard = $data['wildcard'] ?? '*';
    }
}