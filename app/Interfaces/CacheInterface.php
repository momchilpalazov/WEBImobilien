<?php

namespace App\Interfaces;

interface CacheInterface
{
    /**
     * Get an item from the cache
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store an item in the cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Remove an item from the cache
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Clear all items from the cache
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * Get multiple cache items
     *
     * @param array $keys
     * @return array
     */
    public function getMultiple(array $keys): array;

    /**
     * Store multiple items in the cache
     *
     * @param array $values
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public function setMultiple(array $values, int $ttl = 3600): bool;

    /**
     * Remove multiple items from the cache
     *
     * @param array $keys
     * @return bool
     */
    public function deleteMultiple(array $keys): bool;

    /**
     * Check if an item exists in the cache
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;
} 