<?php

namespace Alisa\Events;

use Alisa\Events\Group;
use Alisa\Support\Concerns\WithAnyHandler;
use Alisa\Support\Concerns\WithFallbackHandler;

class Scene extends Group
{
    use WithFallbackHandler, WithAnyHandler;
}
