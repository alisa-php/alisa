<?php

namespace Alisa\Support;

use Alisa\Config;

class Storage
{
    protected string $path;

    public function __construct(?string $path = null, ?string $folder = null)
    {
        if (!$folder) {
            $folder = '__unsorted__';
        }

        if (!$path) {
            $subfolder = Config::get('skill_id') ?: '_default';
            $this->path = sys_get_temp_dir() . '/alisa/' . $subfolder . '/' . trim($folder, '\/');
        } else {
            $this->path = rtrim($path, '\/');
        }

        if (!file_exists($this->path)) {
            mkdir($this->path, recursive: true);
        }
    }

    public function set(string $key, mixed $value): static
    {
        file_put_contents($this->path . '/' . $key, json_encode($value), LOCK_EX);

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return json_decode(file_get_contents($this->path . '/' . $key), true);
    }

    public function has(string $key): bool
    {
        return file_exists($this->path . '/' . $key);
    }

    public function remove(string $key): bool
    {
        return unlink($this->path . '/' . $key);
    }
}