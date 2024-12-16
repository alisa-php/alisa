<?php

namespace Alisa\Scenes;

use Alisa\Events\EventManager;

class Scene extends EventManager
{
    public function __construct(protected string $id)
    {
        Stage::add($this);
    }

    public function getId(): string
    {
        return $this->id;
    }
}