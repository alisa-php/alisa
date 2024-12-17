<?php

namespace Alisa;

use Alisa\Entities\DatetimeEntity;
use Alisa\Entities\Entity;
use Alisa\Entities\FioEntity;
use Alisa\Entities\GeoEntity;
use Alisa\Entities\NumberEntity;
use Alisa\Events\EventManager;
use Alisa\Exceptions\AlisaException;
use Alisa\Http\Request;
use Alisa\Http\Response;
use Alisa\Scenes\Stage;
use Alisa\Sessions\AbstractSession;
use Alisa\Sessions\Application;
use Alisa\Sessions\Session;
use Alisa\Sessions\User;
use Alisa\Stores\Assets;
use Alisa\Stores\Buttons;
use Alisa\Stores\Middlewares;
use Throwable;

class Alisa extends EventManager
{
    protected Request $request;

    public function __construct(array $config = [])
    {
        Config::fill($config);

        $this->setRequest(new Request(Config::get('request')));

        if (!Config::get('skill_id') && ($skillId = $this->request->get('session.skill_id'))) {
            Config::set('skill_id', $skillId);
        }

        if ($this->request->isPing()) {
            exit((new Response)->pong());
        }

        $this->mapEntities();

        $this->loadSessions();
        $this->loadStores();

        $this->registerComponents();
    }

    protected function loadSessions(): void
    {
        // https://yandex.ru/dev/dialogs/alice/doc/ru/session-persistence#store-session
        Session::configure($this->request);

        // https://yandex.ru/dev/dialogs/alice/doc/ru/session-persistence#store-between-sessions
        User::configure($this->request);

        // https://yandex.ru/dev/dialogs/alice/doc/ru/session-persistence#store-application
        Application::configure($this->request);
    }

    protected function mapEntities(): void
    {
        // https://yandex.ru/dev/dialogs/alice/doc/naming-entities.html
        foreach ($this->request->get('request.nlu.entities', []) as $key => $entity) {
            $this->request->set('request.nlu.entities.' . $key, match ($entity['type']) {
                'YANDEX.FIO' => new FioEntity($entity, $this->request),
                'YANDEX.GEO' => new GeoEntity($entity, $this->request),
                'YANDEX.NUMBER' => new NumberEntity($entity, $this->request),
                'YANDEX.DATETIME' => new DatetimeEntity($entity, $this->request),
                default => new Entity($entity, $this->request),
            });
        }
    }

    protected function loadStores(): void
    {
        Middlewares::load(Config::get('middlewares', []));
        Assets::load(Config::get('assets', []));
        Buttons::load(Config::get('buttons', []));
    }

    public function registerComponents(): void
    {
        foreach (Config::get('components') as $key => $value) {
            // [Component::class]
            if (is_numeric($key) && is_string($value)) {
                $component = new $value($this);
            }

            // [Component::class, ['foo' => 'bar']]
            else if (is_string($key)) {
                $component = new $key($this, $value);
            }

            if (!$component instanceof Component) {
                throw new AlisaException('Component must be an instance of ' . Component::class . '.');
            }

            $component->register();
        }
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function run(): void
    {
        try {
            $this->currentScene()->dispatch($this->request);

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
        } catch (Throwable $th) {
            throw $th;
        }
    }

    protected function currentScene(): EventManager
    {
        $sceneKey = Config::get('scene.key');

        if (!$sceneKey) {
            throw AlisaException::invalidSceneKey();
        }

        $driver = new (Config::get('scene.driver'));

        if (!$driver instanceof AbstractSession) {
            throw AlisaException::invalidSceneDriver();
        }

        if (($sceneId = $driver::get($sceneKey)) && $scene = Stage::get($sceneId)) {
            return $scene;
        }

        return $this;
    }
}