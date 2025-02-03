<?php

namespace App\Config;

class Config {
    private static array $config = [];
    
    public static function load(string $file): void {
        if (!file_exists($file)) {
            throw new \RuntimeException("Config file not found: $file");
        }
        
        $config = require $file;
        
        if (!is_array($config)) {
            throw new \RuntimeException("Invalid config file: $file");
        }
        
        self::$config = array_merge(self::$config, $config);
    }
    
    public static function get(string $key, $default = null) {
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }
        
        return $value;
    }
    
    public static function set(string $key, $value): void {
        $keys = explode('.', $key);
        $config = &self::$config;
        
        while (count($keys) > 1) {
            $key = array_shift($keys);
            
            if (!isset($config[$key]) || !is_array($config[$key])) {
                $config[$key] = [];
            }
            
            $config = &$config[$key];
        }
        
        $config[array_shift($keys)] = $value;
    }
    
    public static function has(string $key): bool {
        return self::get($key) !== null;
    }
} 