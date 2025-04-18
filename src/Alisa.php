<?php

namespace Alisa;

use Alisa\Events\HasEvents;
use Alisa\Events\HasDialogEvents;
use Alisa\Events\HasMiddleware;
use Alisa\Http\Request;
use Alisa\Http\Response;
use Alisa\Scenes\Scene;
use Alisa\Scenes\Stage;
use Alisa\Sessions;
use Alisa\Stores;
use Alisa\Support\After;

class Alisa
{
    use HasEvents {
        HasEvents::dispatch as dispatchEvent;
    }
    use HasDialogEvents;
    use HasMiddleware;

    public protected(set) Configuration $config;

    public protected(set) Context $context;

    public function __construct(Configuration $config = new Configuration, Request $request = new Request)
    {
        $this->config = $config;
        $this->context = new Context($request);

        $this->handlePingRequest($request);
        $this->initializeSessions($request);
        $this->initializeStores($config);
        $this->configureSkillId($request);
    }

    public function dispatch(): void
    {
        $scene = $this->resolveCurrentScene();

        if ($scene) {
            $scene->dispatch($this->context);
        } else {
            $this->dispatchEvent($this->context);
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        After::run();
    }

    protected function resolveCurrentScene(): ?Scene
    {
        $sceneId = Sessions\Session::get('__scene__');

        if (!$sceneId) {
            return null;
        }

        if (!Stage::has($sceneId)) {
            Sessions\Session::remove('__scene__');
            return null;
        }

        return Stage::get($sceneId);
    }

    protected function handlePingRequest(Request $request): void
    {
        if ($request->isPing()) {
            exit((new Response)->pong());
        }
    }

    protected function configureSkillId(Request $request): void
    {
        if (!$this->config->get('skill_id') && ($skillId = $request->get('session.skill_id'))) {
            $this->config->set('skill_id', $skillId);
        }
    }

    protected function initializeSessions(Request $request): void
    {
        /**
         * @see https://yandex.ru/dev/dialogs/alice/doc/ru/session-persistence#store-session
         */
        Sessions\Session::initialize($request);

        /**
         * @see https://yandex.ru/dev/dialogs/alice/doc/ru/session-persistence#store-between-sessions
         */
        Sessions\User::initialize($request);

        /**
         * @see https://yandex.ru/dev/dialogs/alice/doc/ru/session-persistence#store-application
         */
        Sessions\Application::initialize($request);
    }

    protected function initializeStores(Configuration $config): void
    {
        Stores\Middlewares::load($config->get('middlewares', []));
        Stores\Assets::load($config->get('assets', []));
        Stores\Buttons::load($config->get('buttons', []));
    }

    public function onScene(string $id, callable $callback): static
    {
        $scene = new Scene($id);

        Stage::add($scene);

        execute($callback, $scene);

        return $this;
    }
}