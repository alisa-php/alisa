<?php

namespace Alisa;

abstract class Component
{
    public function __construct(protected Alisa $alisa, protected Context $context)
    {
        //
    }

    abstract public function register();
}