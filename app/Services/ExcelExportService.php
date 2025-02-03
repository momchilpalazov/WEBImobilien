<?php

namespace App\Services;

class ExcelExportService
{
    public function exportProperties(array $properties, array $types, array $statuses): string
    {
        // Създаване на временен файл
        $tempFile = tempnam(sys_get_temp_dir(), 'properties_export_');
        
        // Отваряне на файла за писане
        $handle = fopen($tempFile, 'w');
        
        // Добавяне на BOM за правилно разпознаване на UTF-8
        fwrite($handle, "\xEF\xBB\xBF");
        
        // Заглавен ред
        fputcsv($handle, [
            'ID',
            'Заглавие (BG)',
            'Заглавие (DE)',
            'Заглавие (RU)',
            'Тип',
            'Статус',
            'Цена (EUR)',
            'Площ (m²)',
            'Локация',
            'Адрес',
            'Година на строеж',
            'Последен ремонт',
            'Етажи',
            'Стаи',
            'Бани',
            'Паркоместа',
            'Създаден',
            'Последна промяна'
        ]);
        
        // Данни за имотите
        foreach ($properties as $property) {
            fputcsv($handle, [
                $property->id,
                $property->title_bg,
                $property->title_de,
                $property->title_ru,
                $types[$property->type] ?? $property->type,
                $statuses[$property->status] ?? $property->status,
                number_format($property->price, 2, '.', ''),
                number_format($property->area, 2, '.', ''),
                $property->location,
                $property->address,
                $property->built_year,
                $property->last_renovation,
                $property->floors,
                $property->rooms,
                $property->bathrooms,
                $property->parking_spaces,
                date('Y-m-d H:i:s', strtotime($property->created_at)),
                date('Y-m-d H:i:s', strtotime($property->updated_at))
            ]);
        }
        
        fclose($handle);
        
        return $tempFile;
    }

    public function exportStatistics(array $stats, array $types, array $statuses): string
    {
        // Създаване на временен файл
        $tempFile = tempnam(sys_get_temp_dir(), 'property_statistics_');
        
        // Отваряне на файла за писане
        $handle = fopen($tempFile, 'w');
        
        // Добавяне на BOM за правилно разпознаване на UTF-8
        fwrite($handle, "\xEF\xBB\xBF");

        // Общи показатели
        fputcsv($handle, ['Общи показатели']);
        fputcsv($handle, ['Общ брой имоти', number_format($stats['total'])]);
        fputcsv($handle, ['Обща стойност', number_format($stats['total_value']) . ' €']);
        fputcsv($handle, ['Средна цена', number_format($stats['avg_price']) . ' €']);
        fputcsv($handle, ['Средна площ', number_format($stats['avg_area']) . ' m²']);
        fputcsv($handle, []);

        // Статистика по тип имот
        fputcsv($handle, ['Статистика по тип имот']);
        fputcsv($handle, ['Тип', 'Брой', 'Обща стойност', 'Средна цена', 'Средна площ']);
        foreach ($stats['by_type'] as $type => $data) {
            if ($data['count'] > 0) {
                fputcsv($handle, [
                    $types[$type],
                    number_format($data['count']),
                    number_format($data['total_value']) . ' €',
                    number_format($data['avg_price']) . ' €',
                    number_format($data['avg_area']) . ' m²'
                ]);
            }
        }
        fputcsv($handle, []);

        // Статистика по статус
        fputcsv($handle, ['Статистика по статус']);
        fputcsv($handle, ['Статус', 'Брой', 'Обща стойност']);
        foreach ($stats['by_status'] as $status => $data) {
            if ($data['count'] > 0) {
                fputcsv($handle, [
                    $statuses[$status],
                    number_format($data['count']),
                    number_format($data['total_value']) . ' €'
                ]);
            }
        }
        fputcsv($handle, []);

        // Ценови диапазони
        fputcsv($handle, ['Ценови диапазони']);
        fputcsv($handle, ['Диапазон', 'Брой имоти']);
        foreach ($stats['price_ranges'] as $range => $count) {
            fputcsv($handle, [
                str_replace('-', ' - ', $range) . ' €',
                number_format($count)
            ]);
        }
        fputcsv($handle, []);

        // Диапазони по площ
        fputcsv($handle, ['Диапазони по площ']);
        fputcsv($handle, ['Диапазон', 'Брой имоти']);
        foreach ($stats['area_ranges'] as $range => $count) {
            fputcsv($handle, [
                str_replace('-', ' - ', $range) . ' m²',
                number_format($count)
            ]);
        }
        fputcsv($handle, []);

        // Месечна статистика
        fputcsv($handle, ['Месечна статистика']);
        fputcsv($handle, ['Месец', 'Брой имоти', 'Обща стойност']);
        foreach ($stats['monthly_stats'] as $month => $data) {
            fputcsv($handle, [
                date('F Y', strtotime($month . '-01')),
                number_format($data['count']),
                number_format($data['total_value']) . ' €'
            ]);
        }

        fclose($handle);
        
        return $tempFile;
    }
} 