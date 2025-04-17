<?php

namespace Alisa;

use Alisa\Http\Request;

class Context
{
    public protected(set) Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function __clone()
    {
        $this->request = clone $this->request;
    }

    public function respond(string $text): void
    {
        dump("[respond] > {$text}");
    }

    public function respondWith(): void
    {
        //
    }
}