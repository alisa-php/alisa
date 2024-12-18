<?php

namespace Alisa\Sessions;

use Alisa\Http\Request;

class Session extends AbstractSession
{
    protected static string $id;

    protected static bool $isNew;

    protected static array $items = [];

    public static function configure(Request $request): void
    {
        static::$id = $request->get('session.session_id');
        static::$isNew = $request->get('session.new') === true;
        static::load($request->get('state.session', []));
    }

    /**
     * Уникальный идентификатор сессии, максимум 64 символа.
     *
     * @return string
     */
    public static function getId(): string
    {
        return static::$id;
    }

    public static function isNew(): bool
    {
        return static::$isNew;
    }
}