<?php

namespace Alisa;

abstract class Component
{
    public function __construct(
        protected Alisa $alisa,
        protected array $args = []
    ) {
        //
    }

    abstract public function register();
}