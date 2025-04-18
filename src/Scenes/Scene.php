<?php

namespace Alisa\Scenes;

use Alisa\Events\HasDialogEvents;
use Alisa\Events\HasEvents;
use Alisa\Events\HasMiddleware;
use Closure;

class Scene
{
    use HasEvents;
    use HasDialogEvents;
    use HasMiddleware;

    public protected(set) string $id;

    public protected(set) Closure|array|string|null $onEnterHandler = null;

    public protected(set) Closure|array|string|null $onLeaveHandler = null;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function onEnter(Closure|array|string|null $handler = null): static
    {
        $this->onEnterHandler = $handler;

        return $this;
    }

    public function onLeave(Closure|array|string|null $handler = null): static
    {
        $this->onLeaveHandler = $handler;

        return $this;
    }
}