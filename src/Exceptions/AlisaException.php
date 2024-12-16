<?php

namespace Alisa\Exceptions;

use Exception;
use RuntimeException;

class AlisaException extends Exception
{
    public static function invalidSceneKey()
    {
        return new RuntimeException('The scene key is invalid in [scene.key].');
    }

    public static function invalidSceneDriver()
    {
        return new RuntimeException('The scene driver is invalid in [scene.driver].');
    }
}