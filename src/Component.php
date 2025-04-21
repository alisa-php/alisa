<?php

namespace Alisa;

use Alisa\Http\Request;
use Alisa\Support\Getter;

abstract class Component
{
    public function __construct(
        protected Getter $arguments = new Getter
    ) {
        //
    }

    abstract public function register(Alisa $alisa, Context $context, Request $request): void;
}