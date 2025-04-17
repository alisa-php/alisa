<?php

namespace Alisa\Support;

use InvalidArgumentException;

/**
 * Позволяет последовательно применять цепочку функций к входному значению (пайплайн).
 *
 * Поддерживает:
 * - Имена функций ('trim', 'strtoupper', и т.п.)
 * - Callable-массивы ([Класс::class, 'метод'], [$объект, 'метод'])
 * - Анонимные функции (Closure)
 * - Invokable-объекты (объекты с методом __invoke())
 */
class Pipeline
{
    /**
     * @var array Последовательность callback-функций для выполнения
     */
    private array $callbacks;

    /**
     * @param array $callbacks Начальный набор callback-функций (необязательно)
     */
    public function __construct(array $callbacks = [])
    {
        $this->callbacks = $callbacks;
    }

    /**
     * @param callable $callback Может быть:
     *     - строка (имя функции)
     *     - массив ([класс, метод] или [объект, метод])
     *     - Closure
     *     - Объект с методом __invoke()
     * @return static
     */
    public function pipe(callable $callback): static
    {
        $this->callbacks[] = $callback;
        return $this;
    }

    /**
     * @param mixed $input Входное значение для обработки
     * @return mixed Результат после применения всех callback-функций
     *
     * @throws InvalidArgumentException Если попался невызываемый элемент
     */
    public function process(mixed $input): mixed
    {
        $next = function ($currentInput) use (&$next) {
            if (empty($this->callbacks)) {
                return $currentInput;
            }

            $currentCallback = array_shift($this->callbacks);

            if (is_string($currentCallback) || is_array($currentCallback)) {
                $result = call_user_func($currentCallback, $currentInput);
                return $next($result);
            }

            if (is_callable($currentCallback)) {
                return $currentCallback($currentInput, $next);
            }

            throw new InvalidArgumentException('Невозможно вызвать: ' . gettype($currentCallback));
        };

        return $next($input);
    }

    /**
     * @param array $callbacks Начальный набор callback-функций (необязательно)
     * @return static
     */
    public static function make(array $callbacks = []): static
    {
        return new static($callbacks);
    }
}