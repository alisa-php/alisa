<?php

namespace Alisa;

use Alisa\Http\Request;

class Alisa
{
    public protected(set) Configuration $config;

    public function __construct(Configuration $config = new Configuration)
    {
        $this->config = $config;
    }

    public function dispatch(Request $request = new Request): void
    {
        dd($request->get('foo'));
    }
}