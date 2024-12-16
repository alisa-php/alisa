<?php

namespace Alisa\Entities;

class FioEntity extends Entity
{
    public function firstName(): ?string
    {
        return $this->entity['value']['first_name'] ?? null;
    }

    public function middleName(): ?string
    {
        return $this->entity['value']['first_name'] ?? null;
    }

    public function lastName(): ?string
    {
        return $this->entity['value']['first_name'] ?? null;
    }

    public function fullName(): string
    {
        return implode(' ', array_filter([
            $this->firstName(),
            $this->middleName(),
            $this->lastName()])
        );
    }

    public function __toString(): string
    {
        return $this->fullName();
    }
}