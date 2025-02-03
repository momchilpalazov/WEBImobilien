<?php

namespace App\Services;

use App\Interfaces\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array<string, callable>
     */
    private array $factories = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->instances[$id]);
    }

    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new \Exception("Service '$id' not found in container");
        }

        if (!isset($this->instances[$id])) {
            $this->instances[$id] = $this->factories[$id]($this);
        }

        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]);
    }
} 