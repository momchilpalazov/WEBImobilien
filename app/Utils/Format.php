<?php

namespace App\Utils;

class Format
{
    /**
     * Get the appropriate Font Awesome icon class for a file type
     */
    public static function fileIcon(string $fileType): string
    {
        return match (strtolower($fileType)) {
            'pdf' => 'pdf',
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'ppt', 'pptx' => 'powerpoint',
            'jpg', 'jpeg', 'png', 'gif' => 'image',
            'zip', 'rar', '7z' => 'archive',
            'txt' => 'text',
            default => 'alt'
        };
    }

    /**
     * Format file size to human readable format
     */
    public static function fileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Format date according to specified format
     */
    public static function date(?string $date, string $format = 'd.m.Y'): string
    {
        if (!$date) {
            return '';
        }
        return date($format, strtotime($date));
    }

    /**
     * Format entity type to human readable format
     */
    public static function entityType(string $type): string
    {
        return match ($type) {
            'property' => 'Имот',
            'deal' => 'Сделка',
            'client' => 'Клиент',
            'agent' => 'Агент',
            default => ucfirst($type)
        };
    }
} 