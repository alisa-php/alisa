<?php

namespace Alisa\Exceptions;

use Exception;
use RuntimeException;

class AlisaException extends Exception
{
    public static function invalidSceneKey()
    {
        return new RuntimeException('Установите корректный ключ сцены [scene.key].');
    }

    public static function invalidSceneDriver()
    {
        return new RuntimeException('Драйвер сцены должен быть классом сессии [scene.driver].');
    }
}