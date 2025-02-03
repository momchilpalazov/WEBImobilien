<?php

namespace App\Services;

use App\Interfaces\CacheInterface;

class FileCache implements CacheInterface
{
    private string $cacheDir;
    private string $prefix;

    public function __construct(string $cacheDir = null, string $prefix = 'cache_')
    {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../../storage/cache/';
        $this->prefix = $prefix;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $filename = $this->getCacheFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $data = $this->readCache($filename);
        if ($data === false || ($data['ttl'] > 0 && time() > $data['ttl'])) {
            unlink($filename);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $filename = $this->getCacheFilename($key);
        $data = [
            'key' => $key,
            'value' => $value,
            'ttl' => $ttl > 0 ? time() + $ttl : 0
        ];

        return $this->writeCache($filename, $data);
    }

    public function delete(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . $this->prefix . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    public function getMultiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    public function deleteMultiple(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    public function has(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        if (!file_exists($filename)) {
            return false;
        }

        $data = $this->readCache($filename);
        if ($data === false || ($data['ttl'] > 0 && time() > $data['ttl'])) {
            unlink($filename);
            return false;
        }

        return true;
    }

    private function getCacheFilename(string $key): string
    {
        return $this->cacheDir . $this->prefix . md5($key) . '.cache';
    }

    private function readCache(string $filename): array|false
    {
        $content = file_get_contents($filename);
        if ($content === false) {
            return false;
        }

        $data = unserialize($content);
        if ($data === false) {
            return false;
        }

        return $data;
    }

    private function writeCache(string $filename, array $data): bool
    {
        return file_put_contents($filename, serialize($data)) !== false;
    }

    public function gc(): void
    {
        $files = glob($this->cacheDir . $this->prefix . '*');
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $data = $this->readCache($file);
            if ($data === false || ($data['ttl'] > 0 && time() > $data['ttl'])) {
                unlink($file);
            }
        }
    }
} 