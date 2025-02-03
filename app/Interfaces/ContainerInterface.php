<?php

namespace App\Interfaces;

interface ContainerInterface
{
    /**
     * Register a service in the container
     *
     * @param string $id Service identifier
     * @param callable $factory Factory function that creates the service
     * @return void
     */
    public function set(string $id, callable $factory): void;

    /**
     * Get a service from the container
     *
     * @param string $id Service identifier
     * @return mixed
     * @throws \Exception If service is not found
     */
    public function get(string $id);

    /**
     * Check if a service exists in the container
     *
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool;
} 