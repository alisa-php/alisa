<?php

namespace Alisa\Types\Request\Meta;

use Alisa\Support\Collection;

class Interfaces extends Collection
{
    /**
     * Пользователь может видеть ответ навыка на экране и открывать ссылки в браузере.
     *
     * @return bool
     */
    public function hasScreen(): bool
    {
        return $this->has('screen');
    }

    /**
     * У пользователя есть возможность запросить связку аккаунтов.
     *
     * @see https://yandex.ru/dev/dialogs/alice/doc/ru/auth/when-to-use
     *
     * @return bool
     */
    public function canAccountLinking(): bool
    {
        return $this->has('account_linking');
    }

    /**
     * На устройстве пользователя есть аудиоплеер.
     *
     * @return bool
     */
    public function hasAudioPlayer(): bool
    {
        return $this->has('audio_player');
    }
}