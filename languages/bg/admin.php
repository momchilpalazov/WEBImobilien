<?php

return [
    'properties' => [
        'title' => 'Управление на имоти',
        'create' => 'Добави имот',
        'edit' => 'Редактирай имот',
        'delete' => 'Изтрий имот',
        'list' => 'Списък с имоти',
        'no_results' => 'Няма намерени имоти',
        'confirm_delete' => 'Сигурни ли сте, че искате да изтриете този имот?',
        
        'form' => [
            'basic_info' => 'Основна информация',
            'title' => 'Заглавие',
            'description' => 'Описание',
            'type' => 'Тип имот',
            'status' => 'Статус',
            'price' => 'Цена',
            'area' => 'Площ',
            'location' => 'Локация',
            'address' => 'Адрес',
            
            'features' => 'Характеристики',
            'built_year' => 'Година на строеж',
            'last_renovation' => 'Последен ремонт',
            'floors' => 'Етажи',
            'parking_spots' => 'Паркоместа',
            'ceiling_height' => 'Височина на тавана',
            'office_space' => 'Офис площ',
            'storage_space' => 'Складова площ',
            'production_space' => 'Производствена площ',
            
            'utilities' => 'Удобства',
            'heating' => 'Отопление',
            'electricity' => 'Електричество',
            'water_supply' => 'Вода',
            'security' => 'Охрана',
            'loading_docks' => 'Товарни рампи',
            
            'media' => 'Медия',
            'images' => 'Изображения',
            'main_image' => 'Основно изображение',
            'additional_images' => 'Допълнителни изображения',
            'virtual_tour' => 'Виртуална разходка',
            'virtual_tour_url' => 'URL на виртуална разходка',
            
            'seo' => 'SEO Оптимизация',
            'meta_title' => 'Meta заглавие',
            'meta_description' => 'Meta описание',
            
            'save' => 'Запази',
            'cancel' => 'Отказ'
        ],
        
        'messages' => [
            'created' => 'Имотът беше създаден успешно.',
            'updated' => 'Имотът беше обновен успешно.',
            'deleted' => 'Имотът беше изтрит успешно.',
            'error' => 'Възникна грешка при обработката на заявката.',
            'image_upload_error' => 'Възникна грешка при качването на изображението.',
            'required_fields' => 'Моля, попълнете всички задължителни полета.',
            'invalid_price' => 'Моля, въведете валидна цена.',
            'invalid_area' => 'Моля, въведете валидна площ.',
            'invalid_year' => 'Моля, въведете валидна година.'
        ],
        
        'table' => [
            'id' => 'ID',
            'title' => 'Заглавие',
            'type' => 'Тип',
            'status' => 'Статус',
            'price' => 'Цена',
            'area' => 'Площ',
            'location' => 'Локация',
            'created_at' => 'Създаден на',
            'actions' => 'Действия'
        ]
    ]
]; 