<?php

namespace App\Helpers;

class Format
{
    /**
     * Форматира размер на файл в човешки четим формат
     */
    public static function fileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $exp = floor(log($bytes) / log(1024));
        
        return round($bytes / pow(1024, $exp), 2) . ' ' . $units[$exp];
    }
    
    /**
     * Връща иконка според типа на файла
     */
    public static function fileIcon(string $mimeType): string
    {
        $icons = [
            'application/pdf' => 'pdf',
            'application/msword' => 'word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
            'image/jpeg' => 'image',
            'image/png' => 'image',
            'text/plain' => 'text',
            'application/vnd.ms-excel' => 'excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel'
        ];
        
        return $icons[$mimeType] ?? 'file';
    }
    
    /**
     * Форматира тип на entity
     */
    public static function entityType(?string $type): string
    {
        $types = [
            'property' => 'Имот',
            'client' => 'Клиент',
            'contract' => 'Договор'
        ];
        
        return $types[$type] ?? '-';
    }
    
    /**
     * Форматира дата и час
     */
    public static function date(?string $date, string $format = 'd.m.Y'): string
    {
        if (!$date) {
            return '-';
        }
        return date($format, strtotime($date));
    }
    
    /**
     * Форматира цена
     */
    public static function price(float $price): string
    {
        return number_format($price, 2, '.', ' ') . ' €';
    }
    
    /**
     * Форматира площ
     */
    public static function area(float $area): string
    {
        return number_format($area, 2, '.', ' ') . ' м²';
    }
    
    /**
     * Форматира процент
     */
    public static function percent(float $value): string
    {
        return round($value) . '%';
    }
    
    /**
     * Форматира статус
     */
    public static function status(string $status): string
    {
        $statuses = [
            'active' => 'Активен',
            'inactive' => 'Неактивен',
            'pending' => 'В изчакване',
            'completed' => 'Завършен',
            'cancelled' => 'Отказан'
        ];
        
        return $statuses[$status] ?? $status;
    }
    
    /**
     * Форматира булева стойност
     */
    public static function boolean(bool $value): string
    {
        return $value ? 'Да' : 'Не';
    }

    /**
     * Форматира число като валута
     */
    public static function currency(float $amount, string $currency = 'EUR'): string
    {
        return number_format($amount, 2) . ' ' . $currency;
    }
} 