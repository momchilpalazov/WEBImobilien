<?php

namespace App\Cache;

class Cache {
    private string $cachePath;
    private int $defaultTtl;
    
    public function __construct(string $cachePath = null, int $defaultTtl = 3600) {
        $this->cachePath = $cachePath ?? __DIR__ . '/../../cache';
        $this->defaultTtl = $defaultTtl;
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    public function get(string $key) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if ($data['expires'] < time()) {
            unlink($filename);
            return false;
        }
        
        return $data['value'];
    }
    
    public function set(string $key, $value, int $ttl = null): bool {
        $filename = $this->getFilename($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + ($ttl ?? $this->defaultTtl)
        ];
        
        return file_put_contents($filename, serialize($data)) !== false;
    }
    
    public function delete(string $key): bool {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    public function clear(): bool {
        $files = glob($this->cachePath . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    private function getFilename(string $key): string {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }
} 