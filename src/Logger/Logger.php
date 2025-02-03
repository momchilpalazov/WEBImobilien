<?php

namespace App\Logger;

class Logger {
    private string $logPath;
    
    public function __construct(string $logPath = null) {
        $this->logPath = $logPath ?? __DIR__ . '/../../logs';
        
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    public function error(string $message, array $context = []): void {
        $this->log('ERROR', $message, $context);
    }
    
    public function info(string $message, array $context = []): void {
        $this->log('INFO', $message, $context);
    }
    
    public function debug(string $message, array $context = []): void {
        $this->log('DEBUG', $message, $context);
    }
    
    private function log(string $level, string $message, array $context = []): void {
        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            $date,
            $level,
            $message,
            $contextStr
        );
        
        $filename = $this->logPath . '/' . date('Y-m-d') . '.log';
        file_put_contents($filename, $logEntry, FILE_APPEND);
    }
    
    public function getLogFiles(): array {
        return glob($this->logPath . '/*.log');
    }
    
    public function clearLogs(): void {
        foreach ($this->getLogFiles() as $file) {
            unlink($file);
        }
    }
} 