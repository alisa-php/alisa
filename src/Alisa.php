<?php

namespace Alisa;

use Alisa\Events\Dispatcher;
use Alisa\Events\Event;
use Alisa\Events\Group;
use Alisa\Events\Scene;
use Alisa\Support\Asset;
use Alisa\Support\Buttons;
use Alisa\Support\Collection;
use Alisa\Support\Storage;
use Alisa\Yandex\Entities\DatetimeEntity;
use Alisa\Yandex\Entities\Entity;
use Alisa\Yandex\Entities\FioEntity;
use Alisa\Yandex\Entities\GeoEntity;
use Alisa\Yandex\Entities\NumberEntity;
use Alisa\Yandex\Image;
use Alisa\Yandex\Sessions\Application;
use Alisa\Yandex\Sessions\Session;
use Alisa\Yandex\Sessions\User;
use Alisa\Yandex\Sound;
use Closure;
use Throwable;

use function Alisa\Support\Helpers\array_flatten;
use function Alisa\Support\Helpers\call_handler;

class Alisa
{
    protected Context $context;

    protected Config $config;

    protected Storage $storage;

    protected Dispatcher $dispatcher;

    protected Closure|array|string|null $onErrorHandler = null;

    protected array $onAfterRunHandlers = [];

    protected array $onBeforeRunHandlers = [];

    protected Image $image;

    protected Sound $sound;

    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
        $this->context = new Context($this->config->get('payload'));
        $this->dispatcher = new Dispatcher;
        $this->storage = new Storage($this->config->get('storage'), $this->config->get('skill_id'));

        // https://yandex.ru/dev/dialogs/alice/doc/health-check.html
        if ($this->context->isPing()) {
            exit($this->context->finish('pong'));
        }

        // https://yandex.ru/dev/dialogs/alice/doc/ru/session-persistence#store-session
        Session::load($this->context->get('state.session', []));

        // https://yandex.ru/dev/dialogs/alice/doc/ru/session-persistence#store-application
        Application::load($this->context->get('state.application', []));

        // https://yandex.ru/dev/dialogs/alice/doc/ru/session-persistence#store-between-sessions
        User::load($this->context->get('state.user', []));

        // https://yandex.ru/dev/dialogs/alice/doc/naming-entities.html
        foreach ($this->context->get('request.nlu.entities', []) as $key => $entity) {
            $this->context->set('request.nlu.entities.'.$key, match ($entity['type']) {
                'YANDEX.FIO' => new FioEntity($entity, $this->context),
                'YANDEX.GEO' => new GeoEntity($entity, $this->context),
                'YANDEX.NUMBER' => new NumberEntity($entity, $this->context),
                'YANDEX.DATETIME' => new DatetimeEntity($entity, $this->context),
                default => new Entity($entity, $this->context),
            });
        }

        $this->components($this->config->get('components', []));
        $this->middleware($this->config->get('middlewares', []));

        Asset::load($this->config->get('assets', []));
        Buttons::load($this->config->get('buttons', []));
    }

    public function components(array $components): static
    {
        foreach ($components as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                $component = new $value($this, $this->context);
                $component->handle();
            } else if (is_string($key) && is_array($value)) {
                $component = new $key($this, $this->context);
                $component->register(...$value);
            }
        }

        return $this;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function context(): Context
    {
        return $this->context;
    }

    public function storage(): Storage
    {
        return $this->storage;
    }

    public function image(): Image
    {
        return isset($this->image) ? $this->image : $this->image = new Image($this);
    }

    public function sound(): Sound
    {
        return isset($this->sound) ? $this->sound : $this->sound = new Sound($this);
    }

    public function scene(string $name, Closure $callback): Scene
    {
        return $this->dispatcher->scene($name, $callback);
    }

    public function group(Closure $callback, int $priority = 0): Group
    {
        return $this->dispatcher->group($callback, $priority);
    }

    public function middleware(Closure|array|string $callback): static
    {
        if (!is_array($callback)) {
            $callback = [$callback];
        }

        foreach ($callback as $middleware) {
            $this->dispatcher->middleware($middleware);
        }

        return $this;
    }

    public function on(Closure|array|string $pattern, Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->on($pattern, $handler, $priority);
    }

    public function onStart(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onStart($handler, $priority);
    }

    public function onCommand(array|string $command, Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onCommand($command, $handler, $priority);
    }

    public function onAction(array|string $action, Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onAction($action, $handler, $priority);
    }

    public function onIntent(array|string $id, Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onIntent($id, $handler, $priority);
    }

    public function onConfirm(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onConfirm($handler, $priority);
    }

    public function onReject(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onReject($handler, $priority);
    }

    public function onHelp(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onHelp($handler, $priority);
    }

    public function onRepeat(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onRepeat($handler, $priority);
    }

    public function onWhatCanYouDo(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onWhatCanYouDo($handler, $priority);
    }

    public function onDangerous(Closure|array|string $handler, int $priority = 0): Event
    {
        return $this->dispatcher->onDangerous($handler, $priority);
    }

    public function onAny(Closure|array|string $handler, int $priority = 0): static
    {
        $this->dispatcher->onAny($handler, $priority);

        return $this;
    }

    public function onFallback(Closure|array|string $handler): static
    {
        $this->dispatcher->onFallback($handler);

        return $this;
    }

    public function onError(Closure|array|string $callback): static
    {
        $this->onErrorHandler = $callback;

        return $this;
    }

    public function onBeforeRun(Closure|array|string $handler, int $priority = 0): static
    {
        $this->onBeforeRunHandlers[$priority][] = $handler;

        return $this;
    }

    public function onAfterRun(Closure|array|string $handler, int $priority = 0): static
    {
        $this->onAfterRunHandlers[$priority][] = $handler;

        return $this;
    }

    public function run(): void
    {
        try {
            foreach (array_flatten($this->onBeforeRunHandlers) as $handler) {
                call_handler($handler, $this->context);
            }

            $this->dispatcher->dispatch($this->context);

            foreach (array_flatten($this->onAfterRunHandlers) as $handler) {
                call_handler($handler, $this->context);
            }
        } catch (Throwable $th) {
            if ($this->onErrorHandler) {
                call_handler($this->onErrorHandler, $this->context, $th);
            } else {
                throw $th;
            }
        }
    }
}