<?php

namespace Alisa\Sessions;

use Alisa\Http\Request;

class Application extends AbstractSession
{
    protected static string $id;

    protected static array $items = [];

    public static function configure(Request $request): void
    {
        static::$id = $request->get('session.application.application_id');
        static::load($request->get('state.session', []));
    }

    /**
     * Идентификатор экземпляра приложения, в котором
     * пользователь общается с Алисой, максимум 64 символа.
     *
     * Даже если пользователь вошел в один и тот же аккаунт в
     * приложение Яндекс для Android и iOS, Яндекс Диалоги присвоят
     * отдельный `application_id` каждому из этих приложений.
     * Этот идентификатор уникален для пары «приложение — навык»:
     * в разных навыках значение свойства `application_id` для одного
     * и того же пользователя будет различаться.
     *
     * @return string|null
     */
    public function getId(): string
    {
        return static::$id;
    }
}