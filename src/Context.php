<?php

namespace Alisa;

use Alisa\Types\Directives\AudioPlayer\AudioPlayer;
use Alisa\Exceptions\AlisaException;
use Alisa\Http\Request;
use Alisa\Http\Response;
use Alisa\Scenes\Stage;
use Alisa\Sessions\Application;
use Alisa\Sessions\Session;
use Alisa\Sessions\User;
use Alisa\Support\Render;
use Alisa\Types\Card\AbstractCard;
use Alisa\Types\Meta\Interfaces;
use Alisa\Types\Nlu\Entities\Entities;
use Alisa\Types\Nlu\Intents\Intents;
use Alisa\Types\Nlu\Tokens\Tokens;

class Context
{
    public protected(set) Request $request;

    public protected(set) Session $session;

    public protected(set) User $user;

    public protected(set) Application $application;

    public protected(set) Tokens $tokens;

    public protected(set) Entities $entities;

    public protected(set) Intents $intents;

    public protected(set) ?string $locale;

    public protected(set) ?string $timezone;

    public protected(set) ?string $useragent;

    public protected(set) ?Interfaces $interfaces;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->session = new Session;
        $this->user = new User;
        $this->application = new Application;

        $this->tokens = $request->get('request.nlu.tokens');
        $this->entities = $request->get('request.nlu.entities');
        $this->intents = $request->get('request.nlu.intents');

        $this->locale = $request->get('meta.locale');
        $this->timezone = $request->get('meta.timezone');
        $this->useragent = $request->get('meta.client_id');
        $this->interfaces = $request->get('meta.interfaces');
    }

    public function __clone()
    {
        $this->request = clone $this->request;
    }

    /**
     * @param string $text
     * @param string|null $tts
     * @param \Alisa\Types\Button[]|string $buttons
     * @param bool $finish
     * @return void
     */
    public function respond(string $text, ?string $tts = null, array|string $buttons = [], bool $finish = false): void
    {
        $processed = Render::process([
            'text' => $text,
            'tts' => $tts ?? $text,
        ]);

        echo (new Response)
            ->text($processed['text'])
            ->tts($processed['tts'])
            ->buttons($buttons)
            ->finish($finish);
    }

    /**
     * @param \Alisa\Types\Card\AbstractCard|\Alisa\Types\Directives\AudioPlayer\AudioPlayer $type
     * @param string $text
     * @param string|null $tts
     * @param bool $finish
     * @return void
     */
    public function respondWith(AbstractCard|AudioPlayer $type, string $text = '', ?string $tts = null, bool $finish = false): void
    {
        $processed = Render::process([
            'text' => $text,
            'tts' => $tts ?? $text,
        ]);

        $response = new Response;

        if ($type instanceof AbstractCard) {
            $response->withCard($type);
        }

        if ($type instanceof AudioPlayer) {
            $response->withAudioPlayer($type);
        }

        echo $response
            ->text($processed['text'])
            ->tts($processed['tts'])
            ->finish($finish);
    }

    /**
     * Вход в указанную сцену по идентификатору.
     *
     * Выдает исключение, если сцена не существует.
     * Устанавливает сцену сеанса и запускает обработчик событий при входе, если он доступен.
     *
     * @param string $id Идентификатор сцены, в которую нужно войти.
     * @return static Возвращает текущий экземпляр контекста для цепочки методов.
     * @throws AlisaException Если сцена не найдена.
     */
    public function enter(string $id): static
    {
        if (!Stage::has($id)) {
            throw new AlisaException("Сцена '{$id}' не существует, создайте ее сначала");
        }

        Session::set('__scene__', $id);

        $scene = Stage::get($id);

        if ($scene->onEnterHandler) {
            execute($scene->onEnterHandler, $this);
        }

        return $this;
    }

    /**
     * Выход из текущей сцены.

     * Если ID сцены не существует, то будет выброшено исключение.
     * Если сцена существует, то будет вызван обработчик выхода, если он доступен.
     *
     * @param string|null $id Нужен для выполнения обработчика выхода.
     * @return static Возвращает текущий экземпляр контекста для цепочки методов.
     * @throws AlisaException Если сцена не найдена.
     */
    public function leave(?string $id = null): static
    {
        Session::remove('__scene__');

        if ($id !== null) {
            if (!Stage::has($id)) {
                throw new AlisaException("Сцена '{$id}' не существует, создайте ее сначала");
            }

            $scene = Stage::get($id);

            if ($scene->onLeaveHandler) {
                execute($scene->onLeaveHandler, $this);
            }
        }

        return $this;
    }
}