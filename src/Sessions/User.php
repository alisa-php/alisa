<?php

namespace Alisa\Sessions;

use Alisa\Http\Request;

class User extends AbstractSession
{
    protected static string $id;

    protected static ?string $accessToken;

    protected static array $items = [];

    public static function configure(Request $request): void
    {
        static::$id = $request->get('session.user.user_id');
        static::$accessToken = $request->get('session.user.access_token');
        static::load($request->get('state.user', []));
    }

    /**
     * Идентификатор пользователя Яндекса, единый для всех
     * приложений и устройств.
     *
     * Этот идентификатор уникален для пары «пользователь — навык»:
     * в разных навыках значение свойства `user_id` для одного и
     * того же пользователя будет различаться.
     *
     * Этот идентификатор можно использовать, например, как уникальный
     * ключ в БД для пользователя.
     *
     * @return string
     */
    public function getId(): string
    {
        return static::$id;
    }

    /**
     * Токен для OAuth-авторизации, который также передается
     * в заголовке `Authorization` для навыков с настроенной
     * связкой аккаунтов.
     *
     * Это JSON-свойство можно использовать, например, при реализации
     * навыка в Yandex Cloud Functions (Диалоги вызывают функции
     * с параметром `integration=raw`, который не позволяет получать
     * заголовки клиентского запроса).
     *
     * @see https://yandex.ru/dev/dialogs/alice/doc/ru/auth/when-to-use
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return static::$id;
    }
}