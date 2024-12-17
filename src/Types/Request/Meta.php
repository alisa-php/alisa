<?php

namespace Alisa\Types\Request;

use Alisa\Support\Collection;
use Alisa\Types\Request\Meta\Interfaces;

class Meta extends Collection
{
    /**
     * Язык в POSIX-формате, максимум 64 символа.
     *
     * "locale": "ru-RU",
     *
     * @return string|null
     */
    public function getLocale(): string
    {
        return $this->get('locale');
    }

    /**
     * Проверка языка.
     *
     * @return bool
     */
    public function isLocale(string $locale): bool
    {
        return $this->getLocale() === $locale;
    }

    /**
     * Название часового пояса, включая алиасы, максимум 64 символа.
     *
     * "timezone": "UTC",
     *
     * @return string|null
     */
    public function getTimezone(): ?string
    {
        return $this->get('timezone');
    }

    /**
     * Не рекомендуется к использованию. Интерфейсы, доступные
     * на клиентском устройстве, перечислены в свойстве `interfaces`.
     *
     * "client_id": "ru.yandex.searchplugin/7.16 (none none; android 4.4.2)",
     *
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->get('client_id');
    }

    /**
     * Интерфейсы, доступные на устройстве пользователя.
     *
     * "interfaces": {
     *   "screen": {},
     *   "payments": {},
     *   "account_linking": {}
     * }
     *
     * @return Interfaces
     */
    public function getInterfaces(): Interfaces
    {
        return new Interfaces($this->get('interfaces', []));
    }
}