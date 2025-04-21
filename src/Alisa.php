<?php

namespace Alisa;

use Alisa\Events\HasEvents;
use Alisa\Events\HasDialogEvents;
use Alisa\Events\HasMiddleware;
use Alisa\Exceptions\AlisaException;
use Alisa\Http\Request;
use Alisa\Http\Response;
use Alisa\Scenes\Scene;
use Alisa\Scenes\Stage;
use Alisa\Services\Image;
use Alisa\Services\Sound;
use Alisa\Sessions;
use Alisa\Stores;
use Alisa\Stores\Assets;
use Alisa\Stores\Buttons;
use Alisa\Stores\Middlewares;
use Alisa\Support\After;
use Alisa\Support\Getter;
use Alisa\Support\Pipeline;
use Alisa\Support\Storage;

class Alisa
{
    use HasEvents {
        HasEvents::dispatch as dispatchEvent;
    }
    use HasDialogEvents;
    use HasMiddleware;

    public protected(set) Configuration $config;

    public protected(set) Context $context;

    public protected(set) Request $request;

    public protected(set) ?Image $image = null;

    public protected(set) ?Sound $sound = null;

    public protected(set) Storage $storage;

    public function __construct(Configuration $config = new Configuration)
    {
        $this->config = $config;

        if ($config->has('payload')) {
            $payloadPath = $config->get('payload');

            if (!file_exists($payloadPath)) {
                throw new AlisaException('Файл запроса не существует: ' . $payloadPath);
            }

            $request = new Request(json_decode(file_get_contents($payloadPath), true));
        } else {
            $request = new Request;
        }

        $this->request = $request;
        $this->context = new Context($request);

        $this->handlePingRequest($request);
        $this->initializeSessions($request);
        $this->initializeStores($config);
        $this->configureSkillId($request);

        if ($token = $config->get('token')) {
            $this->image = new Image($token, $config->get('skill_id'));
            $this->sound = new Sound($token, $config->get('skill_id'));
        }

        $this->storage = new Storage($config->get('storage'));

        Assets::load($config->get('assets', []));
        Buttons::load($config->get('buttons', []));
        Middlewares::load($config->get('middlewares', []));

        $this->registerComponents($this->config->get('components'));
    }

    public function dispatch(): void
    {
        $scene = $this->resolveCurrentScene();

        if ($scene) {
            $callbacks = [...$this->middlewares, fn ($context) => $scene->dispatch($context)];
            Pipeline::make($callbacks)->process($this->context);
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

    public function registerComponents(array $components): void
    {
        foreach ($components as $key => $value) {
            // [Component::class]
            if (is_numeric($key) && is_string($value)) {
                $component = new $value();
            }

            // [Component::class, ['foo' => 'bar']]
            else if (is_string($key)) {
                $component = new $key(new Getter($value));
            }

            if (!$component instanceof Component) {
                throw new AlisaException('Компонент должен быть экземпляром ' . Component::class);
            }

            $component->register($this, $this->context, $this->request);
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

    public function onScene(string $id, callable $callback): void
    {
        $scene = new Scene($id);

        Stage::add($scene);

        execute($callback, $scene);
    }
}