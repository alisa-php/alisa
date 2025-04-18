<?php

namespace Alisa\Scenes;

use Alisa\Exceptions\AlisaException;

class Stage
{
    protected static array $scenes = [];

    public static function add(Scene $scene): void
    {
        if (self::has($scene->id)) {
            throw new AlisaException("Сцена '{$scene->id}' уже существует");
        }

        self::$scenes[$scene->id] = $scene;
    }

    public static function get(string $id): ?Scene
    {
        if (!self::has($id)) {
            throw new AlisaException("Сцена '{$id}' не найдена");
        }

        return self::$scenes[$id];
    }

    public static function has(string $id): bool
    {
        return isset(self::$scenes[$id]);
    }

    public static function all(): array
    {
        return self::$scenes;
    }
}