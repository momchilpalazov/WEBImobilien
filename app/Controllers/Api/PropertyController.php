<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\TranslationService;
use App\Interfaces\PropertyRepositoryInterface;
use App\Services\ValidationService;

class PropertyController extends BaseController
{
    private PropertyRepositoryInterface $propertyRepository;
    private ValidationService $validator;

    public function __construct(
        TranslationService $translationService,
        PropertyRepositoryInterface $propertyRepository,
        ValidationService $validator
    ) {
        parent::__construct($translationService);
        $this->propertyRepository = $propertyRepository;
        $this->validator = $validator;
    }

    public function index(): void
    {
        // Get filter parameters
        $filters = [
            'type' => $_GET['type'] ?? 'all',
            'status' => $_GET['status'] ?? 'all',
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'min_area' => $_GET['min_area'] ?? null,
            'max_area' => $_GET['max_area'] ?? null
        ];

        // Validate numeric filters
        foreach (['min_price', 'max_price', 'min_area', 'max_area'] as $field) {
            if ($filters[$field] !== null) {
                if (!$this->validator->isNumeric($filters[$field])) {
                    $filters[$field] = null;
                }
            }
        }

        // Get page number
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;

        // Get properties with pagination
        $properties = $this->propertyRepository->findByFilters(
            $filters,
            ($page - 1) * $perPage,
            $perPage
        );

        // Get total count for pagination
        $total = $this->propertyRepository->countByFilters($filters);
        $totalPages = ceil($total / $perPage);

        // Prepare pagination data
        $pagination = [
            'current' => $page,
            'total' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages
        ];

        // Start output buffering to capture the HTML
        ob_start();
        require __DIR__ . '/../../../views/properties/_grid.php';
        $html = ob_get_clean();

        // Start output buffering for pagination
        ob_start();
        require __DIR__ . '/../../../views/properties/_pagination.php';
        $paginationHtml = ob_get_clean();

        // Send JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'html' => $html,
            'pagination' => $paginationHtml,
            'total' => $total
        ]);
    }

    public function contact(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        // Validate contact form
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'message' => $_POST['message'] ?? ''
        ];

        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required'
        ];

        if (!$this->validator->validate($data, $rules)) {
            $this->jsonResponse([
                'success' => false,
                'errors' => array_values($this->validator->getErrors())
            ]);
            return;
        }

        // TODO: Send contact email
        // For now just return success
        $this->jsonResponse([
            'success' => true,
            'message' => $this->translations['contact']['success']
        ]);
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
} 
