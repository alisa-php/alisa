<?php

namespace Alisa\Scenes;

use Alisa\Config;
use Alisa\Exceptions\AlisaException;
use Alisa\Sessions\AbstractSession;
use Alisa\Sessions\Session;
use RuntimeException;

class Stage
{
    protected static array $scenes = [];

    public static function add(Scene $scene): void
    {
        self::$scenes[$scene->getId()] = $scene;
    }

    public static function get(string $id): ?Scene
    {
        return self::has($id) ? self::$scenes[$id] : null;
    }

    public static function has(string $id): bool
    {
        return isset(self::$scenes[$id]);
    }

    public static function all(): array
    {
        return self::toArray();
    }

    public static function toArray(): array
    {
        return self::$scenes;
    }

    public static function enter(string $id): void
    {
        $key = Config::get('scene.key');

        if (!$key) {
            throw AlisaException::invalidSceneKey();
        }

        $driver = new (Config::get('scene.driver'));

        if (!$driver instanceof AbstractSession) {
            throw AlisaException::invalidSceneDriver();
        }

        $driver::set($key, $id);
    }

    public static function leave(string $id): void
    {
        $key = Config::get('scene.key');

        if (!$key) {
            throw AlisaException::invalidSceneKey();
        }

        $driver = new (Config::get('scene.driver'));

        if (!$driver instanceof AbstractSession) {
            throw AlisaException::invalidSceneDriver();
        }

        Session::remove($key, $id);
    }
}