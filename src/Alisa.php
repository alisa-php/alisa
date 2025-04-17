<?php

namespace Alisa;

use Alisa\Events\HasEvents;
use Alisa\Events\HasDialogEvents;
use Alisa\Events\HasMiddleware;
use Alisa\Http\Request;

class Alisa
{
    use HasEvents {
        HasEvents::dispatch as dispatchEvent;
    }
    use HasDialogEvents;
    use HasMiddleware;

    public protected(set) Configuration $config;

    public function __construct(Configuration $config = new Configuration)
    {
        $this->config = $config;
    }

    public function dispatch(Request $request = new Request): void
    {
        $context = new Context($request);

        $this->dispatchEvent($context);
    }
}