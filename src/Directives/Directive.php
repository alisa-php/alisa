<?php

namespace Alisa\Directives;

abstract class Directive
{
    protected array $directive = [];

    public function toArray(): array
    {
        return $this->directive;
    }
}