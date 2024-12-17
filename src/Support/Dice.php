<?php

namespace Alisa\Support;

use InvalidArgumentException;

class Dice
{
    /**
     * Выполняет взвешенный случайный выбор на основе процентных вероятностей.
     *
     * @param array $chances Массив элементов вероятностей с ключами `%` и `result`
     * @return mixed Выбранный результат на основе взвешенных вероятностей
     * @throws InvalidArgumentException Если расчет вероятностей недействителен
     */
    public static function roll(array $chances): mixed
    {
        if (empty($chances)) {
            throw new InvalidArgumentException('Массив вероятностей не может быть пустым');
        }

        $normalizedChances = self::validate($chances);

        $randomValue = mt_rand(1, 10000) / 10000;
        $cumulativeProbability = 0;

        foreach ($normalizedChances as $item) {
            $cumulativeProbability += $item['%'] / 100;

            if ($randomValue <= $cumulativeProbability) {
                return $item['result'];
            }
        }

        return end($normalizedChances)['result'];
    }

    /**
     * Проверяет и нормализует процентные вероятности.
     *
     * @param array $chances Входной массив вероятностей
     * @return array Нормализованный массив вероятностей
     * @throws InvalidArgumentException Если вероятности недействительны
     */
    protected static function validate(array $chances): array
    {
        $totalProbability = 0;
        $normalizedChances = [];

        foreach ($chances as $item) {
            if (!isset($item['%']) || !isset($item['result'])) {
                throw new InvalidArgumentException('Каждая вероятность должна иметь ключи `%` и `result`');
            }

            $percentage = floatval($item['%']);
            if ($percentage < 0) {
                throw new InvalidArgumentException('Процент вероятности не может быть отрицательным');
            }

            $normalizedChances[] = [
                '%' => $percentage,
                'result' => $item['result']
            ];

            $totalProbability += $percentage;
        }

        $roundedTotal = round($totalProbability, 2);
        if ($roundedTotal !== 100.00) {
            throw new InvalidArgumentException(
                sprintf('Общая вероятность должна быть 100%, текущая общая: %.2f%%', $roundedTotal)
            );
        }

        return $normalizedChances;
    }
}
