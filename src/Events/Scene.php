<?php

namespace Alisa\Events;

use Alisa\Events\Group;
use Alisa\Support\Concerns\HasAnyHandler;
use Alisa\Support\Concerns\HasFallbackHandler;

class Scene extends Group
{
    use HasFallbackHandler, HasAnyHandler;
}
