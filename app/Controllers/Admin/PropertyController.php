<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseAdminController;
use App\Services\TranslationService;
use App\Interfaces\PropertyRepositoryInterface;
use App\Services\ValidationService;
use App\Services\FileUploadService;
use App\Core\Container;
use App\Interfaces\CacheInterface;
use App\Models\Property;
use App\Services\ExcelExportService;
use App\Services\PropertyStatisticsService;

class PropertyController extends BaseAdminController
{
    private PropertyRepositoryInterface $propertyRepository;
    private ValidationService $validator;
    private FileUploadService $fileUploader;
    private CacheInterface $cache;
    private ExcelExportService $excelExportService;
    private PropertyStatisticsService $statisticsService;
    private const CACHE_TTL = 3600; // 1 hour
    private const PROPERTIES_CACHE_KEY = 'admin.properties.list';

    public function __construct(
        TranslationService $translationService,
        PropertyRepositoryInterface $propertyRepository,
        ValidationService $validator,
        FileUploadService $fileUploader,
        ExcelExportService $excelExportService,
        PropertyStatisticsService $statisticsService
    ) {
        parent::__construct($translationService);
        $this->propertyRepository = $propertyRepository;
        $this->validator = $validator;
        $this->fileUploader = $fileUploader;
        $this->excelExportService = $excelExportService;
        $this->statisticsService = $statisticsService;
        $this->cache = Container::resolve(CacheInterface::class);
    }

    public function index(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Събиране на филтрите от заявката
        $filters = [
            'type' => $_GET['type'] ?? null,
            'status' => $_GET['status'] ?? null,
            'min_price' => !empty($_GET['min_price']) ? (float)$_GET['min_price'] : null,
            'max_price' => !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null,
            'min_area' => !empty($_GET['min_area']) ? (float)$_GET['min_area'] : null,
            'max_area' => !empty($_GET['max_area']) ? (float)$_GET['max_area'] : null,
            'location' => $_GET['location'] ?? null,
            'sort' => $_GET['sort'] ?? 'date_desc'
        ];
        
        // Премахване на празните филтри
        $filters = array_filter($filters);
        
        // Създаване на кеш ключ, базиран на филтрите
        $filterHash = md5(serialize($filters));
        $cacheKey = self::PROPERTIES_CACHE_KEY . ".{$page}.{$filterHash}";
        
        /** @var Property[]|null $properties */
        $properties = $this->cache->get($cacheKey);
        
        if ($properties === null) {
            $properties = $this->propertyRepository->findByFilters($filters, $offset, $limit);
            $this->cache->set($cacheKey, $properties, self::CACHE_TTL);
        }
        
        $totalProperties = $this->propertyRepository->countByFilters($filters);
        $totalPages = ceil($totalProperties / $limit);

        // Добавяне на допълнителни данни за изгледа
        $this->render('admin/properties/index', [
            'properties' => $properties,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'types' => Property::TYPES,
            'statuses' => Property::STATUSES,
            'filters' => $filters,
            'sortOptions' => [
                'date_desc' => 'Най-нови',
                'date_asc' => 'Най-стари',
                'price_desc' => 'Цена (низходяща)',
                'price_asc' => 'Цена (възходяща)',
                'area_desc' => 'Площ (низходяща)',
                'area_asc' => 'Площ (възходяща)'
            ]
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
            return;
        }

        $this->render('admin/properties/form', [
            'property' => null,
            'types' => Property::TYPES,
            'statuses' => Property::STATUSES
        ]);
    }

    public function edit(int $id): void
    {
        /** @var Property|null $property */
        $property = $this->propertyRepository->findById($id);
        
        if (!$property) {
            $this->setError('Property not found');
            $this->redirect('/admin/properties');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate($property);
            return;
        }

        $this->render('admin/properties/form', [
            'property' => $property,
            'types' => Property::TYPES,
            'statuses' => Property::STATUSES
        ]);
    }

    public function delete(int $id): void
    {
        /** @var Property|null $property */
        $property = $this->propertyRepository->findById($id);
        
        if (!$property) {
            $this->setError('Property not found');
        } else {
            try {
                // Delete associated images
                foreach ($property->images as $image) {
                    $this->fileUploader->delete($image);
                }
                
                $this->propertyRepository->delete($id);
                $this->clearPropertyCache();
                $this->setSuccess('Property deleted successfully');
            } catch (\Exception $e) {
                $this->setError('Failed to delete property');
            }
        }
        
        $this->redirect('/admin/properties');
    }

    public function export(): void
    {
        // Събиране на филтрите от заявката
        $filters = [
            'type' => $_GET['type'] ?? null,
            'status' => $_GET['status'] ?? null,
            'min_price' => !empty($_GET['min_price']) ? (float)$_GET['min_price'] : null,
            'max_price' => !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null,
            'min_area' => !empty($_GET['min_area']) ? (float)$_GET['min_area'] : null,
            'max_area' => !empty($_GET['max_area']) ? (float)$_GET['max_area'] : null,
            'location' => $_GET['location'] ?? null,
            'sort' => $_GET['sort'] ?? 'date_desc'
        ];
        
        // Премахване на празните филтри
        $filters = array_filter($filters);
        
        // Вземане на всички имоти, които отговарят на филтрите
        $properties = $this->propertyRepository->findByFilters($filters, 0, PHP_INT_MAX);
        
        try {
            // Експортиране на данните
            $tempFile = $this->excelExportService->exportProperties($properties, Property::TYPES, Property::STATUSES);
            
            // Задаване на headers за download
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="properties_export_' . date('Y-m-d_H-i-s') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Изпращане на файла
            readfile($tempFile);
            
            // Изтриване на временния файл
            unlink($tempFile);
            exit;
        } catch (\Exception $e) {
            $this->setError('Failed to export properties');
            $this->redirect('/admin/properties');
        }
    }

    public function bulkDelete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['selected'])) {
            $this->setError('Не са избрани имоти за изтриване');
            $this->redirect('/admin/properties');
            return;
        }

        $selectedIds = array_map('intval', $_POST['selected']);
        $successCount = 0;
        $errorCount = 0;

        foreach ($selectedIds as $id) {
            /** @var Property|null $property */
            $property = $this->propertyRepository->findById($id);
            
            if ($property) {
                try {
                    // Изтриване на свързаните изображения
                    foreach ($property->images as $image) {
                        $this->fileUploader->delete($image);
                    }
                    
                    $this->propertyRepository->delete($id);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }
        }

        $this->clearPropertyCache();

        if ($successCount > 0) {
            $this->setSuccess("Успешно изтрити {$successCount} имота" . ($errorCount > 0 ? ", {$errorCount} грешки" : ''));
        } else {
            $this->setError('Възникна грешка при изтриването на имотите');
        }

        $this->redirect('/admin/properties');
    }

    public function bulkStatusChange(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
            empty($_POST['selected']) || 
            empty($_GET['status']) ||
            !isset(Property::STATUSES[$_GET['status']])
        ) {
            $this->setError('Невалидни данни за промяна на статуса');
            $this->redirect('/admin/properties');
            return;
        }

        $selectedIds = array_map('intval', $_POST['selected']);
        $newStatus = $_GET['status'];
        $successCount = 0;
        $errorCount = 0;

        foreach ($selectedIds as $id) {
            try {
                $this->propertyRepository->update($id, ['status' => $newStatus]);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        $this->clearPropertyCache();

        if ($successCount > 0) {
            $statusLabel = Property::STATUSES[$newStatus];
            $this->setSuccess(
                "Успешно променен статус на {$successCount} имота на \"{$statusLabel}\"" . 
                ($errorCount > 0 ? ", {$errorCount} грешки" : '')
            );
        } else {
            $this->setError('Възникна грешка при промяната на статуса');
        }

        $this->redirect('/admin/properties');
    }

    public function statistics(): void
    {
        $filters = [];
        
        // Get filters from request
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        if (!empty($_GET['type'])) {
            $filters['type'] = $_GET['type'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        // Generate cache key based on filters
        $cacheKey = self::PROPERTIES_CACHE_KEY . '.statistics.' . md5(serialize($filters));
        
        // Try to get from cache
        $stats = $this->cache->get($cacheKey);
        
        if ($stats === null) {
            $stats = $this->statisticsService->getStatistics($filters);
            $this->cache->set($cacheKey, $stats, self::CACHE_TTL);
        }

        $this->render('admin/properties/statistics', [
            'stats' => $stats,
            'types' => Property::TYPES,
            'statuses' => Property::STATUSES
        ]);
    }

    public function exportStatistics(): void
    {
        try {
            $filters = [];
            
            // Get filters from request
            if (!empty($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (!empty($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            if (!empty($_GET['type'])) {
                $filters['type'] = $_GET['type'];
            }
            if (!empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }

            $stats = $this->statisticsService->getStatistics($filters);
            
            $tempFile = $this->excelExportService->exportStatistics(
                $stats,
                Property::TYPES,
                Property::STATUSES
            );

            $filename = 'property_statistics_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            
            readfile($tempFile);
            unlink($tempFile);
            exit;
            
        } catch (\Exception $e) {
            $this->setError('Грешка при експортиране на статистиката: ' . $e->getMessage());
            $this->redirect('/admin/properties/statistics');
        }
    }

    private function handleCreate(): void
    {
        $data = $this->validateAndPrepareData();
        
        if (!empty($this->errors)) {
            $this->render('admin/properties/form', [
                'property' => null,
                'types' => Property::TYPES,
                'statuses' => Property::STATUSES,
                'oldInput' => $_POST
            ]);
            return;
        }

        try {
            $propertyId = $this->propertyRepository->create($data);
            $this->handleImageUploads($propertyId);
            $this->clearPropertyCache();
            
            $this->setSuccess('Property created successfully');
            $this->redirect('/admin/properties');
        } catch (\Exception $e) {
            $this->setError('Failed to create property');
            $this->render('admin/properties/form', [
                'property' => null,
                'types' => Property::TYPES,
                'statuses' => Property::STATUSES,
                'oldInput' => $_POST
            ]);
        }
    }

    private function handleUpdate(Property $property): void
    {
        $data = $this->validateAndPrepareData();
        
        if (!empty($this->errors)) {
            $this->render('admin/properties/form', [
                'property' => $property,
                'types' => Property::TYPES,
                'statuses' => Property::STATUSES,
                'oldInput' => $_POST
            ]);
            return;
        }

        try {
            $this->propertyRepository->update($property->id, $data);
            $this->handleImageUploads($property->id);
            $this->clearPropertyCache();
            
            $this->setSuccess('Property updated successfully');
            $this->redirect('/admin/properties');
        } catch (\Exception $e) {
            $this->setError('Failed to update property');
            $this->render('admin/properties/form', [
                'property' => $property,
                'types' => Property::TYPES,
                'statuses' => Property::STATUSES,
                'oldInput' => $_POST
            ]);
        }
    }

    private function validateAndPrepareData(): array
    {
        $data = $_POST;
        
        // Basic validation
        if (empty($data['title_bg'])) {
            $this->addError('Title in Bulgarian is required');
        }
        if (empty($data['type']) || !isset(Property::TYPES[$data['type']])) {
            $this->addError('Valid property type is required');
        }
        if (empty($data['status']) || !isset(Property::STATUSES[$data['status']])) {
            $this->addError('Valid status is required');
        }
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            $this->addError('Valid price is required');
        }
        if (!is_numeric($data['area']) || $data['area'] <= 0) {
            $this->addError('Valid area is required');
        }

        // Convert numeric fields
        $data['price'] = (float)$data['price'];
        $data['area'] = (float)$data['area'];
        $data['built_year'] = !empty($data['built_year']) ? (int)$data['built_year'] : null;
        $data['last_renovation'] = !empty($data['last_renovation']) ? (int)$data['last_renovation'] : null;
        $data['floors'] = !empty($data['floors']) ? (int)$data['floors'] : null;
        $data['rooms'] = !empty($data['rooms']) ? (int)$data['rooms'] : null;
        $data['bathrooms'] = !empty($data['bathrooms']) ? (int)$data['bathrooms'] : null;
        $data['parking_spaces'] = !empty($data['parking_spaces']) ? (int)$data['parking_spaces'] : null;

        return $data;
    }

    private function handleImageUploads(int $propertyId): void
    {
        if (!empty($_FILES['images']['name'][0])) {
            /** @var Property|null $property */
            $property = $this->propertyRepository->findById($propertyId);
            if (!$property) {
                return;
            }

            $uploadedFiles = [];
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                try {
                    $originalName = $_FILES['images']['name'][$key];
                    $uploadedFile = $this->fileUploader->upload([
                        'name' => $originalName,
                        'tmp_name' => $tmpName,
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key],
                        'type' => $_FILES['images']['type'][$key]
                    ]);
                    
                    if ($uploadedFile) {
                        $uploadedFiles[] = $uploadedFile;
                    }
                } catch (\Exception $e) {
                    $this->addError("Failed to upload {$originalName}: {$e->getMessage()}");
                }
            }

            if (!empty($uploadedFiles)) {
                $property->images = array_merge($property->images, $uploadedFiles);
                $this->propertyRepository->update($propertyId, ['images' => $property->images]);
            }
        }
    }

    private function clearPropertyCache(): void
    {
        $this->cache->delete(self::PROPERTIES_CACHE_KEY);
        $this->cache->delete(self::PROPERTIES_CACHE_KEY . '.statistics');
        // Clear paginated cache keys
        for ($i = 1; $i <= 10; $i++) { // Clear first 10 pages as a reasonable default
            $this->cache->delete(self::PROPERTIES_CACHE_KEY . ".{$i}");
        }
    }
} 